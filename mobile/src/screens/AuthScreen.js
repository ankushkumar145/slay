import { useState } from 'react';
import { Pressable, ScrollView, Text, TextInput, View } from 'react-native';

import { styles } from '../theme/styles';

export function AuthScreen({ onSubmit, error, setError }) {
  const [mode, setMode] = useState('login');
  const [form, setForm] = useState({ name: '', email: '', password: '' });
  const [loading, setLoading] = useState(false);

  const isRegister = mode === 'register';

  function update(field, value) {
    setForm((current) => ({ ...current, [field]: value }));
  }

  async function submit() {
    setLoading(true);
    setError('');

    try {
      await onSubmit(mode, form);
    } catch (exception) {
      setError(exception.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <ScrollView contentContainerStyle={styles.screen}>
      <View style={styles.authHero}>
        <Text style={styles.logoLarge}>SLAY</Text>
        <Text style={styles.title}>Your dating profile workspace.</Text>
        <Text style={styles.body}>Sign in, build a real profile, then run clean AI analysis from the dashboard.</Text>
      </View>

      <View style={styles.card}>
        <View style={styles.tabs}>
          {[
            ['login', 'Login'],
            ['register', 'Create'],
          ].map(([value, label]) => (
            <Pressable
              key={value}
              style={[styles.tab, mode === value && styles.activeTab]}
              onPress={() => {
                setMode(value);
                setError('');
              }}
            >
              <Text style={styles.tabText}>{label}</Text>
            </Pressable>
          ))}
        </View>

        {isRegister ? (
          <>
            <Text style={styles.formLabel}>Name</Text>
            <TextInput
              value={form.name}
              onChangeText={(value) => update('name', value)}
              placeholder="Your name"
              placeholderTextColor="#958792"
              style={styles.input}
            />
          </>
        ) : null}

        <Text style={styles.formLabel}>Email</Text>
        <TextInput
          value={form.email}
          onChangeText={(value) => update('email', value.trim())}
          autoCapitalize="none"
          keyboardType="email-address"
          placeholder="you@example.com"
          placeholderTextColor="#958792"
          style={styles.input}
        />

        <Text style={styles.formLabel}>Password</Text>
        <TextInput
          value={form.password}
          onChangeText={(value) => update('password', value)}
          secureTextEntry
          placeholder={isRegister ? 'Minimum 8 characters' : 'Your password'}
          placeholderTextColor="#958792"
          style={styles.input}
        />

        {error ? <Text style={styles.notice}>{error}</Text> : null}

        <Pressable style={[styles.primaryButton, loading && styles.disabled]} onPress={submit} disabled={loading}>
          <Text style={styles.primaryButtonText}>{loading ? 'Working...' : isRegister ? 'Create Account' : 'Login'}</Text>
        </Pressable>
      </View>
    </ScrollView>
  );
}
