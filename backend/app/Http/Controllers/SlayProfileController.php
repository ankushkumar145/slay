<?php

namespace App\Http\Controllers;

use App\Services\GroqProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlayProfileController extends Controller
{
    public function questions(): JsonResponse
    {
        return response()->json([
            'data' => $this->questionSet(),
        ]);
    }

    public function analyze(Request $request, GroqProfileService $groq): JsonResponse
    {
        $validated = $request->validate([
            'profile.name' => ['nullable', 'string', 'max:80'],
            'profile.age' => ['nullable', 'integer', 'min:18', 'max:80'],
            'profile.goal' => ['nullable', 'string', 'max:120'],
            'profile.current_bio' => ['nullable', 'string', 'max:600'],
            'profile.target_match' => ['nullable', 'string', 'max:80'],
            'profile.export_style' => ['nullable', 'string', 'max:80'],
            'answers' => ['required', 'array', 'min:10'],
            'answers.*.id' => ['required', 'string'],
            'answers.*.value' => ['required'],
        ]);

        $answers = collect($validated['answers'])->mapWithKeys(fn ($answer) => [
            $answer['id'] => $answer['value'],
        ]);

        $warmth = $this->number($answers->get('warmth'), 6);
        $vulnerability = $this->number($answers->get('vulnerability'), 6);
        $adventure = $this->number($answers->get('adventure'), 6);
        $ambition = $this->number($answers->get('ambition'), 6);
        $socialBattery = $this->number($answers->get('social_battery'), 6);

        $humor = $this->choiceScore($answers->get('humor'), [
            'dry' => 72,
            'playful' => 88,
            'chaotic' => 76,
            'soft' => 80,
        ], 74);

        $replyStyle = $this->choiceScore($answers->get('reply_style'), [
            'fast' => 84,
            'balanced' => 78,
            'slow' => 48,
            'ghosty' => 34,
        ], 65);

        $conflict = $this->choiceScore($answers->get('conflict'), [
            'direct' => 84,
            'cooldown' => 76,
            'avoid' => 42,
            'joke' => 55,
        ], 60);

        $attractiveness = $this->clamp(round(($humor + ($adventure * 8) + ($ambition * 7)) / 3));
        $approachability = $this->clamp(round(($replyStyle + ($warmth * 9) + ($socialBattery * 5)) / 3));
        $emotionalDepth = $this->clamp(round(($conflict + ($vulnerability * 10) + ($warmth * 6)) / 3));
        $clarity = $this->clarityScore($answers);
        $conversationSpark = $this->conversationSparkScore($answers, $humor);

        $archetype = $this->archetype($approachability, $emotionalDepth, $humor, $answers->get('bio_tone'));
        $bioTone = $answers->get('bio_tone', 'witty');
        $dateStyle = $answers->get('date_style', 'coffee_walk');
        $profileName = $validated['profile']['name'] ?? 'You';
        $currentBio = trim($validated['profile']['current_bio'] ?? '');
        $targetMatch = $validated['profile']['target_match'] ?? 'emotionally_available';
        $exportStyle = $validated['profile']['export_style'] ?? 'hinge';
        $beforeBio = $currentBio !== '' ? $currentBio : $this->beforeBio($answers);
        $optimizedBio = $currentBio !== ''
            ? $this->rewriteCurrentBio($profileName, $currentBio, $targetMatch, $dateStyle, $answers)
            : $this->bio($profileName, $bioTone, $dateStyle, $answers);

        $fallback = [
            'archetype' => $archetype,
            'headline' => $this->headline($archetype, $answers),
            'perception' => $this->perception($approachability, $emotionalDepth, $attractiveness),
            'match_fit' => $this->matchFit($answers, $approachability, $emotionalDepth),
            'scores' => [
                'attractiveness' => $attractiveness,
                'approachability' => $approachability,
                'emotional_depth' => $emotionalDepth,
                'profile_clarity' => $clarity,
                'conversation_spark' => $conversationSpark,
            ],
            'before_profile' => $beforeBio,
            'optimized_bio' => $optimizedBio,
            'bio_variants' => $this->bioVariants($profileName, $dateStyle, $answers),
            'profile_diagnosis' => $this->profileDiagnosis($beforeBio, $answers, $targetMatch),
            'target_strategy' => $this->targetStrategy($targetMatch, $answers),
            'platform_exports' => $this->platformExports($optimizedBio, $targetMatch, $exportStyle, $answers),
            'prompt_answers' => [
                [
                    'prompt' => 'Dating me is like...',
                    'answer' => $this->datingMeIsLike($dateStyle, $bioTone),
                ],
                [
                    'prompt' => 'Green flags I bring...',
                    'answer' => $this->greenFlags($answers),
                ],
                [
                    'prompt' => 'My simple pleasure...',
                    'answer' => $this->simplePleasure($answers),
                ],
            ],
            'personality_map' => $this->personalityMap($answers, $approachability, $emotionalDepth),
            'photo_direction' => $this->photoDirection($answers),
            'suggestions' => $this->suggestions($approachability, $emotionalDepth, $answers),
            'share_line' => $this->shareLine($archetype, $attractiveness),
            'export_text' => $this->exportText($archetype, $approachability, $emotionalDepth, $attractiveness, $optimizedBio),
            'ai_generated' => false,
        ];

        $aiResult = $groq->generate([
            'profile' => [
                'name' => $profileName,
                'age' => $validated['profile']['age'] ?? null,
                'goal' => $validated['profile']['goal'] ?? null,
                'current_bio' => $beforeBio,
                'target_match' => $targetMatch,
                'export_style' => $exportStyle,
            ],
            'answers' => $answers->all(),
            'scores' => $fallback['scores'],
        ], $fallback);

        $finalData = $aiResult ?? $fallback;

        if ($request->user()) {
            $request->user()->datingKits()->create([
                'name' => 'Dating Kit - ' . now()->format('M j'),
                'data' => $finalData,
            ]);
        }

        return response()->json([
            'data' => $finalData,
        ]);
    }

    private function questionSet(): array
    {
        return [
            [
                'id' => 'social_battery',
                'section' => 'Personality',
                'type' => 'slider',
                'label' => 'How social do you feel on a good week?',
                'min_label' => 'Low-key',
                'max_label' => 'Main character',
                'default' => 6,
            ],
            [
                'id' => 'energy',
                'section' => 'Personality',
                'type' => 'choice',
                'label' => 'Your default energy is...',
                'options' => [
                    ['value' => 'calm', 'label' => 'Calm'],
                    ['value' => 'playful', 'label' => 'Playful'],
                    ['value' => 'intense', 'label' => 'Intense'],
                    ['value' => 'mysterious', 'label' => 'Mysterious'],
                ],
            ],
            [
                'id' => 'humor',
                'section' => 'Personality',
                'type' => 'choice',
                'label' => 'Your humor lands as...',
                'options' => [
                    ['value' => 'dry', 'label' => 'Dry'],
                    ['value' => 'playful', 'label' => 'Playful'],
                    ['value' => 'chaotic', 'label' => 'Chaotic'],
                    ['value' => 'soft', 'label' => 'Soft'],
                ],
            ],
            [
                'id' => 'planning',
                'section' => 'Lifestyle',
                'type' => 'choice',
                'label' => 'When plans happen, you are usually...',
                'options' => [
                    ['value' => 'planner', 'label' => 'The planner'],
                    ['value' => 'flexible', 'label' => 'Flexible'],
                    ['value' => 'spontaneous', 'label' => 'Spontaneous'],
                    ['value' => 'last_minute', 'label' => 'Last minute'],
                ],
            ],
            [
                'id' => 'date_style',
                'section' => 'Lifestyle',
                'type' => 'choice',
                'label' => 'Best first-date setup?',
                'options' => [
                    ['value' => 'coffee_walk', 'label' => 'Coffee walk'],
                    ['value' => 'food_spot', 'label' => 'Food spot'],
                    ['value' => 'activity', 'label' => 'Activity date'],
                    ['value' => 'night_drive', 'label' => 'Night drive'],
                ],
            ],
            [
                'id' => 'adventure',
                'section' => 'Lifestyle',
                'type' => 'slider',
                'label' => 'How much adventure should your profile signal?',
                'min_label' => 'Cozy routine',
                'max_label' => 'Book the trip',
                'default' => 6,
            ],
            [
                'id' => 'ambition',
                'section' => 'Lifestyle',
                'type' => 'slider',
                'label' => 'How strongly do you want ambition to show?',
                'min_label' => 'Soft life',
                'max_label' => 'Locked in',
                'default' => 7,
            ],
            [
                'id' => 'solo_time',
                'section' => 'Lifestyle',
                'type' => 'choice',
                'label' => 'Your relationship with alone time:',
                'options' => [
                    ['value' => 'need_it', 'label' => 'Need it'],
                    ['value' => 'balanced', 'label' => 'Balanced'],
                    ['value' => 'rarely', 'label' => 'Rarely alone'],
                    ['value' => 'secretly_love', 'label' => 'Secretly love it'],
                ],
            ],
            [
                'id' => 'reply_style',
                'section' => 'Social',
                'type' => 'choice',
                'label' => 'Texting style?',
                'options' => [
                    ['value' => 'fast', 'label' => 'Fast replies'],
                    ['value' => 'balanced', 'label' => 'Balanced'],
                    ['value' => 'slow', 'label' => 'Slow but present'],
                    ['value' => 'ghosty', 'label' => 'Disappears sometimes'],
                ],
            ],
            [
                'id' => 'flirting',
                'section' => 'Social',
                'type' => 'choice',
                'label' => 'How do you flirt?',
                'options' => [
                    ['value' => 'teasing', 'label' => 'Light teasing'],
                    ['value' => 'compliments', 'label' => 'Direct compliments'],
                    ['value' => 'questions', 'label' => 'Curious questions'],
                    ['value' => 'acts', 'label' => 'Tiny helpful acts'],
                ],
            ],
            [
                'id' => 'compliments',
                'section' => 'Social',
                'type' => 'slider',
                'label' => 'How comfortable are you giving compliments?',
                'min_label' => 'Rarely',
                'max_label' => 'Naturally',
                'default' => 6,
            ],
            [
                'id' => 'warmth',
                'section' => 'Emotional',
                'type' => 'slider',
                'label' => 'How warm do people find you at first?',
                'min_label' => 'Reserved',
                'max_label' => 'Instantly warm',
                'default' => 6,
            ],
            [
                'id' => 'vulnerability',
                'section' => 'Emotional',
                'type' => 'slider',
                'label' => 'How easily do you share real feelings?',
                'min_label' => 'Guarded',
                'max_label' => 'Open',
                'default' => 6,
            ],
            [
                'id' => 'conflict',
                'section' => 'Emotional',
                'type' => 'choice',
                'label' => 'When something feels off, you...',
                'options' => [
                    ['value' => 'direct', 'label' => 'Say it clearly'],
                    ['value' => 'cooldown', 'label' => 'Need time first'],
                    ['value' => 'avoid', 'label' => 'Avoid it'],
                    ['value' => 'joke', 'label' => 'Deflect with jokes'],
                ],
            ],
            [
                'id' => 'attachment',
                'section' => 'Emotional',
                'type' => 'choice',
                'label' => 'In romance, you tend to be...',
                'options' => [
                    ['value' => 'secure', 'label' => 'Steady'],
                    ['value' => 'anxious', 'label' => 'Reassurance-seeking'],
                    ['value' => 'avoidant', 'label' => 'Space-first'],
                    ['value' => 'slow_burn', 'label' => 'Slow burn'],
                ],
            ],
            [
                'id' => 'romance_pace',
                'section' => 'Romantic',
                'type' => 'choice',
                'label' => 'Your romantic pace is...',
                'options' => [
                    ['value' => 'slow', 'label' => 'Slow and intentional'],
                    ['value' => 'balanced', 'label' => 'Balanced'],
                    ['value' => 'fast', 'label' => 'Fast chemistry'],
                    ['value' => 'friends_first', 'label' => 'Friends first'],
                ],
            ],
            [
                'id' => 'bio_tone',
                'section' => 'Profile',
                'type' => 'choice',
                'label' => 'Pick your profile tone:',
                'options' => [
                    ['value' => 'witty', 'label' => 'Witty'],
                    ['value' => 'warm', 'label' => 'Warm'],
                    ['value' => 'bold', 'label' => 'Bold'],
                    ['value' => 'mysterious', 'label' => 'Mysterious'],
                ],
            ],
            [
                'id' => 'photo_vibe',
                'section' => 'Profile',
                'type' => 'choice',
                'label' => 'Your photos mostly show...',
                'options' => [
                    ['value' => 'face', 'label' => 'Clear face'],
                    ['value' => 'travel', 'label' => 'Travel/lifestyle'],
                    ['value' => 'friends', 'label' => 'Social proof'],
                    ['value' => 'random', 'label' => 'Random moments'],
                ],
            ],
            [
                'id' => 'profile_goal',
                'section' => 'Profile',
                'type' => 'choice',
                'label' => 'What should your profile improve most?',
                'options' => [
                    ['value' => 'more_matches', 'label' => 'More matches'],
                    ['value' => 'better_matches', 'label' => 'Better matches'],
                    ['value' => 'less_dry', 'label' => 'Less dry chats'],
                    ['value' => 'confidence', 'label' => 'Confidence'],
                ],
            ],
            [
                'id' => 'green_flag',
                'section' => 'Profile',
                'type' => 'choice',
                'label' => 'Your strongest green flag:',
                'options' => [
                    ['value' => 'consistent', 'label' => 'Consistent'],
                    ['value' => 'funny', 'label' => 'Funny'],
                    ['value' => 'thoughtful', 'label' => 'Thoughtful'],
                    ['value' => 'driven', 'label' => 'Driven'],
                ],
            ],
            [
                'id' => 'love_language',
                'section' => 'Romantic',
                'type' => 'choice',
                'label' => 'What makes you feel chosen?',
                'options' => [
                    ['value' => 'quality_time', 'label' => 'Quality time'],
                    ['value' => 'words', 'label' => 'Clear words'],
                    ['value' => 'acts', 'label' => 'Thoughtful acts'],
                    ['value' => 'touch', 'label' => 'Easy affection'],
                ],
            ],
            [
                'id' => 'profile_weakness',
                'section' => 'Profile',
                'type' => 'choice',
                'label' => 'Your current profile probably feels...',
                'options' => [
                    ['value' => 'generic', 'label' => 'Too generic'],
                    ['value' => 'tryhard', 'label' => 'Too try-hard'],
                    ['value' => 'dry', 'label' => 'Too dry'],
                    ['value' => 'unclear', 'label' => 'Too unclear'],
                ],
            ],
            [
                'id' => 'dealbreaker',
                'section' => 'Romantic',
                'type' => 'choice',
                'label' => 'Biggest dating turn-off?',
                'options' => [
                    ['value' => 'inconsistency', 'label' => 'Inconsistency'],
                    ['value' => 'low_effort', 'label' => 'Low effort'],
                    ['value' => 'bad_communication', 'label' => 'Bad communication'],
                    ['value' => 'no_humor', 'label' => 'No humor'],
                ],
            ],
            [
                'id' => 'weekend',
                'section' => 'Lifestyle',
                'type' => 'choice',
                'label' => 'Your ideal weekend has...',
                'options' => [
                    ['value' => 'reset', 'label' => 'A full reset'],
                    ['value' => 'friends', 'label' => 'Friends and food'],
                    ['value' => 'explore', 'label' => 'Something new'],
                    ['value' => 'project', 'label' => 'A personal project'],
                ],
            ],
            [
                'id' => 'conversation_depth',
                'section' => 'Social',
                'type' => 'slider',
                'label' => 'How quickly do you like conversations to get real?',
                'min_label' => 'Keep it light',
                'max_label' => 'Go deep',
                'default' => 7,
            ],
        ];
    }

    private function number(mixed $value, int $fallback): int
    {
        return max(1, min(10, (int) ($value ?? $fallback)));
    }

    private function choiceScore(mixed $value, array $scores, int $fallback): int
    {
        return $scores[$value] ?? $fallback;
    }

    private function clamp(int|float $value): int
    {
        return max(1, min(100, (int) $value));
    }

    private function clarityScore($answers): int
    {
        $photoBoost = match ($answers->get('photo_vibe')) {
            'face' => 88,
            'travel', 'friends' => 76,
            default => 55,
        };

        $weaknessPenalty = match ($answers->get('profile_weakness')) {
            'unclear' => 14,
            'generic' => 10,
            'dry' => 8,
            default => 4,
        };

        return $this->clamp($photoBoost - $weaknessPenalty + ($this->number($answers->get('compliments'), 6) * 3));
    }

    private function conversationSparkScore($answers, int $humor): int
    {
        $depth = $this->number($answers->get('conversation_depth'), 7);
        $flirt = match ($answers->get('flirting')) {
            'teasing' => 84,
            'compliments' => 78,
            'questions' => 82,
            'acts' => 70,
            default => 72,
        };

        return $this->clamp(round(($humor + $flirt + ($depth * 8)) / 3));
    }

    private function archetype(int $approachability, int $depth, int $humor, mixed $tone): string
    {
        if ($tone === 'mysterious' || ($approachability < 58 && $depth > 70)) {
            return 'Magnetic but Hard to Read';
        }

        if ($humor > 82 && $depth < 72) {
            return 'Playful but Emotionally Guarded';
        }

        if ($approachability > 75 && $depth > 72) {
            return 'Warm, Clear, and Easy to Choose';
        }

        if ($depth > 78) {
            return 'Soft-Spoken with Hidden Depth';
        }

        return 'Confident with a Slow-Burn Pull';
    }

    private function perception(int $approachability, int $depth, int $attractiveness): string
    {
        if ($approachability < 55) {
            return 'You likely read as attractive, but slightly distant. The profile needs more warmth and clearer invitation.';
        }

        if ($depth > 78 && $attractiveness > 75) {
            return 'You come across as emotionally layered without feeling heavy. That is a strong signal for people who want more than small talk.';
        }

        return 'You come across as interesting and dateable, but your profile should make your personality easier to picture in real life.';
    }

    private function headline(string $archetype, $answers): string
    {
        $energy = match ($answers->get('energy')) {
            'calm' => 'quiet pull',
            'playful' => 'playful spark',
            'intense' => 'high-intent energy',
            'mysterious' => 'low-volume mystery',
            default => 'clearer romantic signal',
        };

        return "{$archetype} with {$energy}.";
    }

    private function matchFit($answers, int $approachability, int $depth): string
    {
        if ($answers->get('profile_goal') === 'better_matches' || $depth > 74) {
            return 'Best fit: emotionally available people who like playful conversation but still want consistency.';
        }

        if ($approachability < 58) {
            return 'Best fit: confident openers who are not intimidated by a reserved first impression.';
        }

        return 'Best fit: curious people who like plans, banter, and a profile that gives them something specific to ask about.';
    }

    private function beforeBio($answers): string
    {
        return match ($answers->get('profile_weakness')) {
            'tryhard' => 'Ambitious, funny, loves travel and good food.',
            'dry' => 'Ask me anything. Here for good vibes.',
            'unclear' => 'Figuring it out, probably overthinking this bio.',
            default => 'I like music, food, travel, and deep conversations.',
        };
    }

    private function bio(string $name, mixed $tone, mixed $dateStyle, $answers): string
    {
        $date = match ($dateStyle) {
            'food_spot' => 'has strong opinions on food spots',
            'activity' => 'turns dates into tiny side quests',
            'night_drive' => 'romanticizes night drives and playlists',
            default => 'can make a coffee walk feel like a plot twist',
        };

        $greenFlag = match ($answers->get('green_flag')) {
            'consistent' => 'consistent without making it boring',
            'funny' => 'funny when it matters and serious when it counts',
            'thoughtful' => 'thoughtful in the details people usually miss',
            'driven' => 'driven, but still knows how to be present',
            default => 'easy to talk to once the vibe clicks',
        };

        return match ($tone) {
            'warm' => "{$name} is {$greenFlag}, {$date}, and believes the best connections feel calm and exciting at the same time.",
            'bold' => "{$greenFlag}. {$date}. Will probably make you laugh, challenge your playlist, and remember the thing you mentioned once.",
            'mysterious' => "{$date}. Says less at first, notices more than expected, and is worth the second conversation.",
            default => "{$date}, overthinks the caption, still somehow sends the best recommendation. {$greenFlag}.",
        };
    }

    private function bioVariants(string $name, mixed $dateStyle, $answers): array
    {
        $date = match ($dateStyle) {
            'food_spot' => 'food spots',
            'activity' => 'activity dates',
            'night_drive' => 'night drives',
            default => 'coffee walks',
        };

        $greenFlag = match ($answers->get('green_flag')) {
            'consistent' => 'consistent',
            'funny' => 'funny',
            'thoughtful' => 'thoughtful',
            'driven' => 'driven',
            default => 'curious',
        };

        return [
            [
                'style' => 'Witty',
                'text' => "{$name} has range: {$date}, suspiciously good recommendations, and a {$greenFlag} streak that sneaks up on you.",
            ],
            [
                'style' => 'Warm',
                'text' => "{$greenFlag} by nature, better in real conversation, and looking for something that feels easy without being shallow.",
            ],
            [
                'style' => 'Bold',
                'text' => "Strong opinions on {$date}, soft spot for effort, and zero interest in pretending bad communication is mysterious.",
            ],
        ];
    }

    private function rewriteCurrentBio(string $name, string $currentBio, string $targetMatch, mixed $dateStyle, $answers): string
    {
        $target = $this->targetLabel($targetMatch);
        $date = match ($dateStyle) {
            'food_spot' => 'knows the kind of food spot that turns into a second date',
            'activity' => 'likes dates with a little story built in',
            'night_drive' => 'has a soft spot for night drives and honest playlists',
            default => 'can make a coffee walk feel unexpectedly personal',
        };

        $greenFlag = match ($answers->get('green_flag')) {
            'consistent' => 'consistent without making connection feel heavy',
            'funny' => 'funny in a way that still leaves room for real talk',
            'thoughtful' => 'thoughtful about the details people usually miss',
            'driven' => 'driven, but not too busy to be present',
            default => 'curious, warm, and better in real conversation',
        };

        $hook = str($currentBio)->limit(42, '')->trim()->lower();

        return "{$name} gives {$hook} more personality: {$date}, {$greenFlag}, and is looking for {$target}.";
    }

    private function profileDiagnosis(string $bio, $answers, string $targetMatch): array
    {
        $tooGeneric = str_word_count($bio) < 8 || str_contains(strtolower($bio), 'good vibes');
        $target = $this->targetLabel($targetMatch);

        return [
            [
                'label' => 'Current signal',
                'value' => $tooGeneric
                    ? 'Your current profile reads broad, so people cannot picture what dating you actually feels like.'
                    : 'Your current profile has usable material, but it needs sharper romantic context.',
            ],
            [
                'label' => 'Missing pull',
                'value' => 'Add one specific date/lifestyle detail and one emotional green flag.',
            ],
            [
                'label' => 'Target adjustment',
                'value' => "To attract {$target}, signal consistency, warmth, and one easy conversation hook.",
            ],
        ];
    }

    private function targetStrategy(string $targetMatch, $answers): array
    {
        return [
            'target' => $this->targetLabel($targetMatch),
            'lead_signal' => match ($targetMatch) {
                'serious_relationship' => 'Show emotional clarity and low-drama consistency.',
                'playful_dating' => 'Lead with humor, plans, and lightness.',
                'ambitious_partner' => 'Show drive without sounding unavailable.',
                'soft_romantic' => 'Lead with warmth, care, and calm chemistry.',
                default => 'Show that you are easy to approach and steady after the first spark.',
            },
            'avoid' => match ($answers->get('profile_weakness')) {
                'tryhard' => 'Do not over-list achievements. Make the profile feel lived-in.',
                'dry' => 'Do not hide behind minimal lines. Give people something to respond to.',
                'unclear' => 'Do not sound undecided. Pick a romantic signal and commit to it.',
                default => 'Avoid generic claims like travel, music, food, and good vibes without proof.',
            },
        ];
    }

    private function platformExports(string $bio, string $targetMatch, string $preferred, $answers): array
    {
        $target = $this->targetLabel($targetMatch);
        $short = str($bio)->limit(92, '');
        $prompt = $this->greenFlags($answers);

        $exports = [
            [
                'platform' => 'Hinge',
                'recommended' => $preferred === 'hinge',
                'bio' => $bio,
                'prompt' => "Green flags I bring: {$prompt}",
            ],
            [
                'platform' => 'Bumble',
                'recommended' => $preferred === 'bumble',
                'bio' => "{$short}. Looking for {$target}.",
                'prompt' => 'Swipe right if you can hold a real conversation and still laugh at small things.',
            ],
            [
                'platform' => 'Tinder',
                'recommended' => $preferred === 'tinder',
                'bio' => "{$short}. Good banter, better plans.",
                'prompt' => 'First round: coffee walk, food spot, or activity date?',
            ],
            [
                'platform' => 'Instagram',
                'recommended' => $preferred === 'instagram',
                'bio' => str($bio)->limit(120, ''),
                'prompt' => 'Soft launch energy, strong recommendation game.',
            ],
        ];

        return collect($exports)->sortByDesc('recommended')->values()->all();
    }

    private function targetLabel(string $targetMatch): string
    {
        return match ($targetMatch) {
            'serious_relationship' => 'someone serious and emotionally available',
            'playful_dating' => 'someone playful who still shows effort',
            'ambitious_partner' => 'someone ambitious and emotionally present',
            'soft_romantic' => 'someone soft, romantic, and steady',
            default => 'emotionally available people',
        };
    }

    private function datingMeIsLike(mixed $dateStyle, mixed $tone): string
    {
        if ($tone === 'bold') {
            return 'a good playlist with one song you did not expect to love.';
        }

        return match ($dateStyle) {
            'food_spot' => 'finding a tiny food place that becomes your new standard.',
            'activity' => 'saying yes to one plan and ending up with three stories.',
            'night_drive' => 'late-night honesty, good music, and one unnecessarily deep question.',
            default => 'a coffee walk that somehow turns into the best part of the week.',
        };
    }

    private function greenFlags($answers): string
    {
        return match ($answers->get('green_flag')) {
            'consistent' => 'I show up clearly, communicate better than I disappear, and keep my word.',
            'funny' => 'I can make things lighter without making your feelings feel small.',
            'thoughtful' => 'I remember details, notice effort, and care in practical ways.',
            'driven' => 'I have direction, but I am not too busy to be emotionally present.',
            default => 'I am honest, curious, and better in person than on paper.',
        };
    }

    private function simplePleasure($answers): string
    {
        return match ($answers->get('planning')) {
            'planner' => 'a plan that actually leaves room for a little chaos.',
            'spontaneous' => 'random plans that become oddly perfect memories.',
            'last_minute' => 'a last-minute idea that works better than it should.',
            default => 'easy conversation with someone who does not make connection feel like work.',
        };
    }

    private function personalityMap($answers, int $approachability, int $depth): array
    {
        return [
            [
                'label' => 'First impression',
                'value' => $approachability > 70 ? 'Inviting and easy to message' : 'Interesting, but needs more warmth',
            ],
            [
                'label' => 'Romantic signal',
                'value' => $depth > 72 ? 'Emotionally substantial' : 'Light, fun, and still forming depth',
            ],
            [
                'label' => 'Conversation hook',
                'value' => match ($answers->get('flirting')) {
                    'teasing' => 'Banter with a little edge',
                    'compliments' => 'Direct warmth',
                    'questions' => 'Curiosity that opens people up',
                    'acts' => 'Care shown through details',
                    default => 'Specific stories over generic claims',
                },
            ],
        ];
    }

    private function photoDirection($answers): array
    {
        $baseline = [
            'Lead with a clear face photo that feels relaxed, not overly posed.',
            'Add one lifestyle photo that proves your bio instead of repeating it.',
        ];

        if ($answers->get('photo_vibe') === 'friends') {
            $baseline[] = 'Keep social proof, but make sure the first two photos are unmistakably you.';
        } elseif ($answers->get('photo_vibe') === 'random') {
            $baseline[] = 'Replace random screenshots or low-context photos with one clean full-body shot.';
        } else {
            $baseline[] = 'Use the final photo as a conversation trigger: food, hobby, trip, or activity.';
        }

        return $baseline;
    }

    private function suggestions(int $approachability, int $depth, $answers): array
    {
        $suggestions = [];

        if ($approachability < 60) {
            $suggestions[] = 'Add one warmer line that makes you easier to message.';
        }

        if ($depth < 65) {
            $suggestions[] = 'Include one specific emotional signal so the profile does not feel only witty or polished.';
        }

        if ($answers->get('photo_vibe') === 'random') {
            $suggestions[] = 'Use at least one clear face photo and one lifestyle photo with context.';
        }

        if ($answers->get('reply_style') === 'ghosty') {
            $suggestions[] = 'Avoid jokes about disappearing. They may confirm the exact fear your best matches already have.';
        }

        return $suggestions ?: [
            'Your base vibe is strong. Focus on sharper specificity rather than adding more lines.',
            'Lead with one memorable detail instead of a broad trait like adventurous or chill.',
        ];
    }

    private function shareLine(string $archetype, int $attractiveness): string
    {
        return "SLAY says my dating archetype is {$archetype}. Attraction signal: {$attractiveness}/100.";
    }

    private function exportText(string $archetype, int $approachability, int $depth, int $attractiveness, string $bio): string
    {
        return "SLAY result: {$archetype}\nAttractiveness: {$attractiveness}/100\nApproachability: {$approachability}/100\nEmotional Depth: {$depth}/100\n\nOptimized bio:\n{$bio}";
    }
}
