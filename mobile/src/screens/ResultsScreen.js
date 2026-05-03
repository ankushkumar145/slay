import { useMemo, useState } from 'react';
import { Pressable, ScrollView, Share, Text, View } from 'react-native';
import * as Clipboard from 'expo-clipboard';

import { AppHeader } from '../components/AppHeader';
import { ResultList } from '../components/ResultList';
import { styles } from '../theme/styles';

const sections = [
  ['bio', 'Bio'],
  ['exports', 'Exports'],
  ['advice', 'Advice'],
  ['details', 'Details'],
];

export function ResultsScreen({ result, onBack, onRetake }) {
  const [activeVariant, setActiveVariant] = useState(0);
  const [section, setSection] = useState('bio');
  const [copied, setCopied] = useState('');
  const selectedVariant = result.bio_variants?.[activeVariant];
  const scores = useMemo(
    () => [
      ['Attraction', result.scores?.attractiveness],
      ['Approach', result.scores?.approachability],
      ['Depth', result.scores?.emotional_depth],
      ['Clarity', result.scores?.profile_clarity],
      ['Spark', result.scores?.conversation_spark],
    ],
    [result],
  );

  async function shareText(text) {
    await Share.share({ message: text });
  }

  async function copyText(label, text) {
    await Clipboard.setStringAsync(text ?? '');
    setCopied(`${label} copied`);
    setTimeout(() => setCopied(''), 1600);
  }

  return (
    <ScrollView contentContainerStyle={styles.screen}>
      <AppHeader kicker="Results" rightLabel="Dashboard" onRight={onBack} />

      <View style={styles.compactResultHero}>
        <View style={styles.resultHeroTop}>
          <Text style={styles.aiBadge}>{result.ai_generated ? 'Groq AI' : 'Fallback'}</Text>
          <Pressable style={styles.textButton} onPress={() => shareText(result.share_line)}>
            <Text style={styles.textButtonLabel}>Share</Text>
          </Pressable>
        </View>
        <Text style={styles.eyebrow}>Archetype</Text>
        <Text style={styles.compactResultTitle}>{result.archetype}</Text>
        <Text style={styles.resultBody} numberOfLines={2}>{result.headline}</Text>
      </View>

      <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.scoreRail}>
        {scores.map(([label, value]) => (
          <View style={styles.scorePillCard} key={label}>
            <Text style={styles.scorePillValue}>{value}</Text>
            <Text style={styles.scoreLabel}>{label}</Text>
          </View>
        ))}
      </ScrollView>

      <Text style={styles.sectionKicker}>Result kit</Text>
      {copied ? <Text style={styles.successNotice}>{copied}</Text> : null}
      <View style={styles.segmentedTabs}>
        {sections.map(([value, label]) => (
          <Pressable
            key={value}
            style={[styles.segmentedTab, section === value && styles.activeSegmentedTab]}
            onPress={() => setSection(value)}
          >
            <Text style={[styles.segmentedTabText, section === value && styles.activeSegmentedTabText]}>{label}</Text>
          </Pressable>
        ))}
      </View>

      {section === 'bio' ? (
        <View style={styles.card}>
          <View style={styles.exportHeader}>
            <Text style={styles.sectionTitle}>Upgraded bio</Text>
            <Pressable style={styles.textButton} onPress={() => shareText(result.optimized_bio)}>
              <Text style={styles.textButtonLabel}>Share</Text>
            </Pressable>
          </View>
          <Text style={styles.darkBody}>{result.optimized_bio}</Text>
          <Pressable style={styles.secondaryButton} onPress={() => copyText('Bio', result.optimized_bio)}>
            <Text style={styles.secondaryButtonText}>Copy Bio</Text>
          </Pressable>

          <View style={styles.tabs}>
            {result.bio_variants?.map((variant, index) => (
              <Pressable
                key={variant.style}
                style={[styles.tab, index === activeVariant && styles.activeTab]}
                onPress={() => setActiveVariant(index)}
              >
                <Text style={styles.tabText}>{variant.style}</Text>
              </Pressable>
            ))}
          </View>
          <Text style={styles.cardText}>{selectedVariant?.text}</Text>
          <Pressable style={styles.primaryButton} onPress={() => copyText('Full kit', result.export_text)}>
            <Text style={styles.primaryButtonText}>Copy Full Kit</Text>
          </Pressable>
        </View>
      ) : null}

      {section === 'exports' ? (
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Dating app exports</Text>
          {result.platform_exports?.map((item) => (
            <View style={styles.listItem} key={item.platform}>
              <View style={styles.exportHeader}>
                <Text style={styles.cardLabel}>{item.platform}</Text>
                {item.recommended ? <Text style={styles.softBadge}>Best fit</Text> : null}
              </View>
              <Text style={styles.cardText}>{item.bio}</Text>
              <Text style={styles.exportPrompt}>{item.prompt}</Text>
              <Pressable style={styles.secondaryButton} onPress={() => copyText(item.platform, `${item.bio}\n\n${item.prompt}`)}>
                <Text style={styles.secondaryButtonText}>Copy {item.platform}</Text>
              </Pressable>
            </View>
          ))}
        </View>
      ) : null}

      {section === 'advice' ? (
        <>
          <ResultList title="Next Moves" items={result.suggestions} />
          <ResultList title="Photo Direction" items={result.photo_direction} />
        </>
      ) : null}

      {section === 'details' ? (
        <>
          <ResultList title="Profile Diagnosis" items={result.profile_diagnosis} />
          <ResultList title="Prompt Answers" items={result.prompt_answers} />
          <ResultList title="Personality Map" items={result.personality_map} />
        </>
      ) : null}

      <View style={styles.compactActions}>
        <Pressable style={styles.secondaryButton} onPress={onRetake}>
          <Text style={styles.secondaryButtonText}>Retake</Text>
        </Pressable>
      </View>
    </ScrollView>
  );
}
