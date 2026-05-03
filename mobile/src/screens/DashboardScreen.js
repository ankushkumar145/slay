import { Pressable, ScrollView, Text, View } from 'react-native';
import { Ionicons, MaterialCommunityIcons } from '@expo/vector-icons';

import { AppHeader } from '../components/AppHeader';
import { exportStyles, goals, targetMatches } from '../constants';
import { styles } from '../theme/styles';
import { labelFor } from '../utils';

export function DashboardScreen({
  user,
  profile,
  questions,
  result,
  onStartQuiz,
  onOpenGuru,
  onEditProfile,
  onViewResult,
  onLogout,
  error,
}) {
  return (
    <ScrollView contentContainerStyle={styles.screen}>
      <AppHeader kicker="Dashboard" />

      <View style={styles.workbenchHeader}>
        <View>
          <Text style={styles.sectionKicker}>Today</Text>
          <Text style={styles.workbenchTitle}>{user?.name ?? 'Profile owner'}</Text>
          <Text style={styles.workbenchMeta}>{Number(profile.age) >= 18 ? 'Profile ready' : 'Profile incomplete'}</Text>
        </View>
        <Pressable style={styles.headerActionButton} onPress={onStartQuiz}>
          <Ionicons name="sparkles-outline" size={16} color="#d6346f" />
          <Text style={styles.headerActionText}>Analyze</Text>
        </Pressable>
      </View>

      <View style={styles.actionList}>
        <ActionRow
          title="Love Guru"
          body="Get help with replies, date prep, and mixed signals."
          icon="chat-heart-outline"
          family={MaterialCommunityIcons}
          onPress={onOpenGuru}
        />
        <ActionRow
          title={result ? 'Latest kit' : 'Profile setup'}
          body={result ? 'Open your saved bio, exports, and advice.' : 'Set your target, goal, and app style.'}
          icon={result ? 'clipboard-text-outline' : 'account-edit-outline'}
          family={MaterialCommunityIcons}
          onPress={result ? onViewResult : onEditProfile}
        />
      </View>

      <Text style={styles.sectionKicker}>Daily Dating Coach</Text>
      <View style={styles.summaryPanel}>
        <ActionRow
          title="Refresh one prompt today"
          body="Small tweaks keep your profile active."
          icon="lightbulb-on-outline"
          family={MaterialCommunityIcons}
          onPress={() => {}}
        />
      </View>

      <Text style={styles.sectionKicker}>Your setup</Text>
      <View style={styles.summaryPanel}>
        <View style={styles.cardTitleRow}>
          <Text style={styles.sectionTitle}>Profile</Text>
          <Pressable style={styles.inlineEditButton} onPress={onEditProfile}>
            <Ionicons name="pencil" size={14} color="#d6346f" />
            <Text style={styles.inlineEditText}>Edit</Text>
          </Pressable>
        </View>
        <View style={styles.summaryRows}>
          <SummaryRow label="Goal" value={labelFor(goals, profile.goal)} />
          <SummaryRow label="Target" value={labelFor(targetMatches, profile.target_match)} />
          <SummaryRow label="Export" value={labelFor(exportStyles, profile.export_style)} />
        </View>
      </View>

      {error ? <Text style={styles.notice}>{error}</Text> : null}

      <Text style={styles.sectionKicker}>Activity</Text>
      <View style={styles.statStrip}>
        <Metric label="Signals" value={String(questions.length)} icon="sparkles-outline" />
        <Metric
          label="Last read"
          value={result ? 'Saved' : 'None'}
          icon="clipboard-text-outline"
          family={MaterialCommunityIcons}
        />
      </View>

      <Pressable style={styles.logoutButton} onPress={onLogout}>
        <Ionicons name="log-out-outline" size={15} color="#74645c" />
        <Text style={styles.logoutText}>Logout</Text>
      </Pressable>
    </ScrollView>
  );
}

function SummaryRow({ label, value }) {
  return (
    <View style={styles.summaryRow}>
      <Text style={styles.summaryLabel}>{label}</Text>
      <Text style={styles.summaryValue}>{value}</Text>
    </View>
  );
}

function Metric({ label, value, icon, family: IconFamily = Ionicons }) {
  return (
    <View style={styles.metricCard}>
      <IconFamily name={icon} size={16} color="#d6346f" />
      <Text style={styles.metricValue}>{value}</Text>
      <Text style={styles.scoreLabel}>{label}</Text>
    </View>
  );
}

function ActionRow({ title, body, icon, family: IconFamily = Ionicons, onPress }) {
  return (
    <Pressable style={styles.actionRow} onPress={onPress}>
      <View style={styles.actionRowIcon}>
        <IconFamily name={icon} size={18} color="#d6346f" />
      </View>
      <View style={styles.actionRowCopy}>
        <Text style={styles.actionRowTitle}>{title}</Text>
        <Text style={styles.actionRowBody}>{body}</Text>
      </View>
      <Ionicons name="chevron-forward" size={17} color="#9a8b84" />
    </Pressable>
  );
}
