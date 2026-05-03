import { useState } from 'react';
import { Pressable, ScrollView, Text, TextInput, View } from 'react-native';

import { AppHeader } from '../components/AppHeader';
import { exportStyles, goals, targetMatches } from '../constants';
import { styles } from '../theme/styles';

export function ProfileScreen({ initialProfile, onSave, onBack, error, setError }) {
  const [profile, setProfile] = useState(initialProfile);
  const [saving, setSaving] = useState(false);

  function update(field, value) {
    setProfile((current) => ({ ...current, [field]: value }));
  }

  async function save() {
    if (Number(profile.age) < 18) {
      setError('Age must be 18 or older.');
      return;
    }

    setSaving(true);
    setError('');

    try {
      await onSave(profile);
    } catch (exception) {
      setError(exception.message);
    } finally {
      setSaving(false);
    }
  }

  return (
    <ScrollView contentContainerStyle={styles.screen}>
      <AppHeader kicker="Profile setup" rightLabel="Back" onRight={onBack} />

      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Profile base</Text>
        <Text style={styles.formLabel}>Age</Text>
        <TextInput
          value={String(profile.age ?? '')}
          onChangeText={(value) => update('age', value.replace(/\D/g, ''))}
          keyboardType="number-pad"
          placeholder="18+"
          placeholderTextColor="#958792"
          style={styles.input}
        />

        <Text style={styles.formLabel}>Current bio or prompt</Text>
        <TextInput
          value={profile.current_bio ?? ''}
          onChangeText={(value) => update('current_bio', value)}
          placeholder="Paste the profile text you want improved"
          placeholderTextColor="#958792"
          style={[styles.input, styles.textArea]}
          multiline
        />
      </View>

      <ChipSection title="Goal" options={goals} value={profile.goal} onChange={(value) => update('goal', value)} />
      <ChipSection
        title="Target match"
        options={targetMatches}
        value={profile.target_match}
        onChange={(value) => update('target_match', value)}
      />
      <ChipSection
        title="Export style"
        options={exportStyles}
        value={profile.export_style}
        onChange={(value) => update('export_style', value)}
      />

      {error ? <Text style={styles.notice}>{error}</Text> : null}

      <Pressable style={[styles.primaryButton, saving && styles.disabled]} onPress={save} disabled={saving}>
        <Text style={styles.primaryButtonText}>{saving ? 'Saving...' : 'Save Profile'}</Text>
      </Pressable>
    </ScrollView>
  );
}

function ChipSection({ title, options, value, onChange }) {
  return (
    <View style={styles.card}>
      <Text style={styles.sectionTitle}>{title}</Text>
      <View style={styles.goalGrid}>
        {options.map(([optionValue, label]) => (
          <Pressable
            key={optionValue}
            style={[styles.goalChip, value === optionValue && styles.activeChip]}
            onPress={() => onChange(optionValue)}
          >
            <Text style={styles.chipText}>{label}</Text>
          </Pressable>
        ))}
      </View>
    </View>
  );
}
