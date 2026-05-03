export const fallbackQuestions = [
  {
    id: 'warmth',
    section: 'Emotional',
    type: 'slider',
    label: 'How warm do people find you at first?',
    min_label: 'Reserved',
    max_label: 'Instantly warm',
    default: 6,
  },
  {
    id: 'bio_tone',
    section: 'Profile',
    type: 'choice',
    label: 'Pick your profile tone:',
    options: [
      { value: 'witty', label: 'Witty' },
      { value: 'warm', label: 'Warm' },
      { value: 'bold', label: 'Bold' },
      { value: 'mysterious', label: 'Mysterious' },
    ],
  },
];

export const goals = [
  ['better_matches', 'Better matches'],
  ['more_matches', 'More matches'],
  ['less_dry', 'Less dry chats'],
  ['confidence', 'Confidence'],
];

export const targetMatches = [
  ['emotionally_available', 'Emotionally available'],
  ['serious_relationship', 'Serious relationship'],
  ['playful_dating', 'Playful dating'],
  ['ambitious_partner', 'Ambitious partner'],
  ['soft_romantic', 'Soft romantic'],
];

export const exportStyles = [
  ['hinge', 'Hinge'],
  ['bumble', 'Bumble'],
  ['tinder', 'Tinder'],
  ['instagram', 'Instagram'],
];
