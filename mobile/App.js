import { StatusBar } from 'expo-status-bar';
import { useEffect, useMemo, useState } from 'react';
import { ActivityIndicator, Pressable, SafeAreaView, Text, View } from 'react-native';

import { apiRequest, getStoredResult, getStoredToken, saveStoredResult, saveStoredToken } from './src/api';
import { BottomTabs } from './src/components/BottomTabs';
import { fallbackQuestions } from './src/constants';
import { AuthScreen } from './src/screens/AuthScreen';
import { DashboardScreen } from './src/screens/DashboardScreen';
import { GuruScreen } from './src/screens/GuruScreen';
import { ProfileScreen } from './src/screens/ProfileScreen';
import { QuizScreen } from './src/screens/QuizScreen';
import { ResultsScreen } from './src/screens/ResultsScreen';
import { styles } from './src/theme/styles';
import { defaultAnswers, quickQuestions } from './src/utils';

const emptyProfile = {
  age: '',
  goal: 'better_networking',
  current_bio: '',
  target_match: 'industry_leaders',
  export_style: 'linkedin',
};

export default function App() {
  const [booting, setBooting] = useState(true);
  const [token, setToken] = useState('');
  const [user, setUser] = useState(null);
  const [profile, setProfile] = useState(emptyProfile);
  const [questions, setQuestions] = useState([]);
  const [activeQuestions, setActiveQuestions] = useState([]);
  const [answers, setAnswers] = useState({});
  const [screen, setScreen] = useState('auth');
  const [step, setStep] = useState(0);
  const [result, setResult] = useState(null);
  const [guruMessages, setGuruMessages] = useState([]);
  const [guruLoading, setGuruLoading] = useState(false);
  const [guruError, setGuruError] = useState('');
  const [analyzing, setAnalyzing] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    boot();
  }, []);

  const canUseProfile = Number(profile.age) >= 18;
  const quizQuestions = activeQuestions.length ? activeQuestions : questions;
  const currentQuizQuestion = quizQuestions[step];
  const progress = quizQuestions.length ? (step + 1) / quizQuestions.length : 0;
  const profilePayload = useMemo(
    () => ({
      name: user?.name ?? '',
      age: Number(profile.age),
      goal: profile.goal,
      current_bio: profile.current_bio,
      target_match: profile.target_match,
      export_style: profile.export_style,
    }),
    [profile, user],
  );

  async function boot() {
    setBooting(true);
    await loadQuestions();

    const storedToken = getStoredToken();
    if (!storedToken) {
      setBooting(false);
      return;
    }

    try {
      const payload = await apiRequest('/auth/me', { token: storedToken });
      applySession(payload.data, storedToken);
      try {
        const kitPayload = await apiRequest('/kits/latest', { token: storedToken });
        if (kitPayload.data && kitPayload.data.data) {
          setResult(kitPayload.data.data);
        } else {
          const lastResult = getStoredResult();
          if (lastResult) {
            setResult(lastResult);
          }
        }
      } catch {
        const lastResult = getStoredResult();
        if (lastResult) {
          setResult(lastResult);
        }
      }
    } catch {
      saveStoredToken('');
      setScreen('auth');
    } finally {
      setBooting(false);
    }
  }

  async function loadQuestions() {
    try {
      const payload = await apiRequest('/questions');
      const loadedQuestions = payload.data ?? fallbackQuestions;
      setQuestions(loadedQuestions);
      setActiveQuestions(quickQuestions(loadedQuestions));
      setAnswers(defaultAnswers(loadedQuestions));
    } catch {
      setQuestions(fallbackQuestions);
      setActiveQuestions(quickQuestions(fallbackQuestions));
      setAnswers(defaultAnswers(fallbackQuestions));
      setError('Backend is offline. Preview mode is on.');
    }
  }

  function applySession(nextUser, nextToken) {
    const nextProfile = nextUser?.profile ?? {};
    setToken(nextToken);
    setUser(nextUser);
    setProfile({
      ...emptyProfile,
      ...nextProfile,
      age: nextProfile.age ? String(nextProfile.age) : '',
    });
    saveStoredToken(nextToken);
    setScreen(nextProfile?.age ? 'dashboard' : 'profile');
  }

  async function handleAuth(mode, form) {
    setError('');
    const payload = await apiRequest(`/auth/${mode}`, {
      method: 'POST',
      body: form,
    });

    applySession(payload.data.user, payload.data.token);
  }

  async function saveProfile(nextProfile) {
    setError('');
    const payload = await apiRequest('/profile', {
      method: 'PUT',
      token,
      body: {
        ...nextProfile,
        age: Number(nextProfile.age),
      },
    });

    setProfile({ ...payload.data, age: String(payload.data.age ?? '') });
    setUser((current) => (current ? { ...current, profile: payload.data } : current));
    setScreen('dashboard');
  }

  function startQuiz() {
    if (!canUseProfile) {
      setError('Finish your profile first.');
      setScreen('profile');
      return;
    }

    setError('');
    setResult(null);
    setStep(0);
    setActiveQuestions(quickQuestions(questions));
    setAnswers(defaultAnswers(questions));
    setScreen('quiz');
  }

  async function submitAnalysis() {
    setAnalyzing(true);
    setError('');

    try {
      const payload = await apiRequest('/analyze', {
        method: 'POST',
        token,
        body: {
          profile: profilePayload,
          answers: Object.entries(answers).map(([id, value]) => ({ id, value })),
        },
      });

      setResult(payload.data);
      saveStoredResult(payload.data);
      setScreen('results');
    } catch {
      setError('Groq is taking a minute. Check the backend, then try again.');
    } finally {
      setAnalyzing(false);
    }
  }

  async function sendGuruMessage(message) {
    const userMessage = { role: 'user', content: message };
    const nextMessages = [...guruMessages, userMessage];

    setGuruMessages(nextMessages);
    setGuruLoading(true);
    setGuruError('');

    try {
      const payload = await apiRequest('/guru/chat', {
        method: 'POST',
        token,
        body: {
          message,
          context: nextMessages.slice(-6),
        },
      });

      setGuruMessages((current) => [
        ...current,
        {
          role: 'assistant',
          content: payload.data.answer,
          suggested_texts: payload.data.suggested_texts,
          watch_out: payload.data.watch_out,
          next_step: payload.data.next_step,
        },
      ]);
    } catch (exception) {
      setGuruError(exception.message);
    } finally {
      setGuruLoading(false);
    }
  }

  async function logout() {
    if (token) {
      apiRequest('/auth/logout', { method: 'POST', token }).catch(() => {});
    }

    setToken('');
    setUser(null);
    setProfile(emptyProfile);
    setResult(null);
    saveStoredResult(null);
    setScreen('auth');
    saveStoredToken('');
  }

  function selectTab(tab) {
    if (tab === 'quiz') {
      startQuiz();
      return;
    }

    if (tab === 'results' && !result) {
      return;
    }

    setError('');
    setScreen(tab);
  }

  if (booting || !currentQuizQuestion) {
    return (
      <SafeAreaView style={styles.safe}>
        <View style={styles.splashScreen}>
          <View style={styles.splashPanel}>
            <Text style={styles.splashLogo}>SLAY</Text>
            <View style={styles.splashStatusRow}>
              <ActivityIndicator color="#d6346f" size="small" />
              <Text style={styles.splashStatus}>Getting your questions ready</Text>
            </View>
            <View style={styles.splashTrack}>
              <View style={styles.splashFill} />
            </View>
          </View>
        </View>
        <StatusBar style="dark" />
      </SafeAreaView>
    );
  }

  if (screen === 'auth') {
    return (
      <SafeAreaView style={styles.safe}>
        <AuthScreen onSubmit={handleAuth} error={error} setError={setError} />
        <StatusBar style="dark" />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.appShell}>
      {screen === 'profile' ? (
        <ProfileScreen
          initialProfile={profile}
          onSave={saveProfile}
          onBack={() => setScreen(canUseProfile ? 'dashboard' : 'auth')}
          error={error}
          setError={setError}
        />
      ) : null}

      {screen === 'dashboard' ? (
        <DashboardScreen
          user={user}
          profile={profile}
          questions={questions}
          result={result}
          onStartQuiz={startQuiz}
          onOpenGuru={() => setScreen('guru')}
          onEditProfile={() => setScreen('profile')}
          onViewResult={() => setScreen('results')}
          onLogout={logout}
          error={error}
        />
      ) : null}

      {screen === 'guru' ? (
        <GuruScreen
          messages={guruMessages}
          onSend={sendGuruMessage}
          loading={guruLoading}
          error={guruError}
        />
      ) : null}

      {screen === 'results' && !result ? <EmptyKitScreen onStartQuiz={startQuiz} /> : null}

      {screen === 'quiz' ? (
        <QuizScreen
          currentQuestion={currentQuizQuestion}
          questions={quizQuestions}
          step={step}
          progress={progress}
          answers={answers}
          onAnswer={(id, value) => setAnswers((current) => ({ ...current, [id]: value }))}
          onBack={() => setScreen('dashboard')}
          onPrevious={() => setStep((current) => Math.max(current - 1, 0))}
          onNext={() => setStep((current) => Math.min(current + 1, quizQuestions.length - 1))}
          onSubmit={submitAnalysis}
          analyzing={analyzing}
          error={error}
        />
      ) : null}

      {screen === 'results' && result ? (
        <ResultsScreen
          result={result}
          onBack={() => setScreen('dashboard')}
          onRetake={startQuiz}
        />
      ) : null}
        <BottomTabs active={screen} onSelect={selectTab} />
      </View>
      <StatusBar style="dark" />
    </SafeAreaView>
  );
}

function EmptyKitScreen({ onStartQuiz }) {
  return (
    <View style={styles.screen}>
      <View style={styles.dashboardHero}>
        <Text style={styles.eyebrow}>Kit</Text>
        <Text style={styles.title}>No result yet.</Text>
        <Text style={styles.body}>Run an analysis and your bio, app exports, prompt answers, and next moves will live here.</Text>
      </View>
      <Pressable style={styles.primaryButton} onPress={onStartQuiz}>
        <Text style={styles.primaryButtonText}>Start Analysis</Text>
      </Pressable>
    </View>
  );
}
