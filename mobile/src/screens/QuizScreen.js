import { ActivityIndicator, Pressable, ScrollView, Text, View } from 'react-native';

import { AppHeader } from '../components/AppHeader';
import { QuestionRenderer } from '../components/QuestionRenderer';
import { styles } from '../theme/styles';

export function QuizScreen({
  currentQuestion,
  questions,
  step,
  progress,
  answers,
  onAnswer,
  onBack,
  onPrevious,
  onNext,
  onSubmit,
  analyzing,
  error,
}) {
  const isLast = step === questions.length - 1;
  const loadingCopy = step % 3 === 0 ? 'Reading signals...' : step % 3 === 1 ? 'Writing your kit...' : 'Polishing prompts...';

  return (
    <ScrollView contentContainerStyle={styles.screen}>
      <AppHeader kicker="Question flow" rightLabel="Dashboard" onRight={onBack} />

      {analyzing ? (
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', paddingVertical: 60, paddingHorizontal: 24 }}>
          <ActivityIndicator size="large" color="#d6346f" style={{ marginBottom: 24 }} />
          <Text style={{ fontSize: 24, fontWeight: '700', color: '#1f1619', textAlign: 'center', marginBottom: 12 }}>Reading your signals...</Text>
          <Text style={{ fontSize: 15, color: '#74645c', textAlign: 'center', lineHeight: 22 }}>Building your Dating Kit with profile diagnosis, rewritten bios, and the Love Guru's advice.</Text>
        </View>
      ) : (
        <>
          <View style={styles.quizCard}>
            <View style={styles.quizMetaRow}>
              <Text style={styles.quizSectionPill}>{currentQuestion.section}</Text>
              <Text style={styles.progressText}>{step + 1}/{questions.length}</Text>
            </View>
            <View style={styles.progressTrack}>
              <View style={[styles.progressFill, { width: `${progress * 100}%` }]} />
            </View>

            <Text style={styles.signal}>Signal {String(step + 1).padStart(2, '0')}</Text>
            <Text style={styles.question}>{currentQuestion.label}</Text>
            <QuestionRenderer
              question={currentQuestion}
              value={answers[currentQuestion.id]}
              onChange={(value) => onAnswer(currentQuestion.id, value)}
            />
          </View>

          {error ? <Text style={styles.notice}>{error}</Text> : null}

          <View style={styles.actions}>
            <Pressable style={[styles.secondaryButton, step === 0 && styles.disabled]} onPress={onPrevious} disabled={step === 0}>
              <Text style={styles.secondaryButtonText}>Previous</Text>
            </Pressable>
            <Pressable
              style={styles.primaryButton}
              onPress={isLast ? onSubmit : onNext}
            >
              <Text style={styles.primaryButtonText}>{isLast ? 'Generate Kit' : 'Next'}</Text>
            </Pressable>
          </View>
        </>
      )}
    </ScrollView>
  );
}
