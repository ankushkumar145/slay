import { useState } from 'react';
import { Pressable, ScrollView, Text, TextInput, View } from 'react-native';
import { Ionicons, MaterialCommunityIcons } from '@expo/vector-icons';
import * as Clipboard from 'expo-clipboard';

import { AppHeader } from '../components/AppHeader';
import { styles } from '../theme/styles';

const starters = [
  'What should I text after a good first date?',
  'They reply late but seem interested. What do I do?',
  'Help me ask for clarity without sounding needy.',
];

export function GuruScreen({ messages, onSend, loading, error }) {
  const [draft, setDraft] = useState('');
  const [copied, setCopied] = useState('');

  function send(text = draft) {
    const cleanText = text.trim();

    if (!cleanText || loading) {
      return;
    }

    setDraft('');
    onSend(cleanText);
  }

  async function copyReply(text) {
    await Clipboard.setStringAsync(text);
    setCopied('Copied to clipboard');
    setTimeout(() => setCopied(''), 1500);
  }

  return (
    <View style={styles.chatShell}>
      <ScrollView contentContainerStyle={styles.chatContent}>
        <AppHeader kicker="Love Guru" />

        <View style={styles.guruHero}>
          <View style={styles.heroIconBubble}>
            <Ionicons name="heart-outline" size={21} color="#d6346f" />
          </View>
          <Text style={styles.eyebrow}>Chat box</Text>
          <Text style={styles.title}>Ask the Love Guru.</Text>
          <Text style={styles.body}>Texting help, date prep, mixed signals, boundaries, and what-to-say-next.</Text>
        </View>

        {messages.length === 0 ? (
          <>
            <Text style={styles.sectionKicker}>Try asking</Text>
            <View style={styles.starterGrid}>
              {starters.map((starter) => (
                <Pressable style={styles.starterPrompt} key={starter} onPress={() => send(starter)}>
                  <View style={styles.starterIcon}>
                    <Ionicons name="sparkles-outline" size={15} color="#d6346f" />
                  </View>
                  <Text style={styles.starterPromptText}>{starter}</Text>
                </Pressable>
              ))}
            </View>
          </>
        ) : null}

        {copied ? <Text style={styles.successNotice}>{copied}</Text> : null}

        {messages.map((message, index) => (
          <View
            key={`${message.role}-${index}`}
            style={[styles.chatBubble, message.role === 'user' ? styles.userBubble : styles.guruBubble]}
          >
            <Text style={[styles.chatRole, message.role === 'user' && styles.userChatText]}>
              {message.role === 'user' ? 'You' : 'Love Guru'}
            </Text>
            {message.role === 'assistant' ? (
              <MaterialCommunityIcons name="chat-heart-outline" size={15} color="#d6346f" />
            ) : null}
            <Text style={[styles.chatText, message.role === 'user' && styles.userChatText]}>{message.content}</Text>
            {message.suggested_texts?.length ? (
              <View style={styles.suggestedTexts}>
                <Text style={styles.cardLabel}>Try saying</Text>
                {message.suggested_texts.map((text) => (
                  <Pressable style={styles.replyOption} key={text} onPress={() => setDraft(text)}>
                    <Text style={styles.replyOptionText}>{text}</Text>
                    <Pressable style={styles.copyMiniButton} onPress={() => copyReply(text)}>
                      <Text style={styles.copyMiniText}>Copy</Text>
                    </Pressable>
                  </Pressable>
                ))}
              </View>
            ) : null}
            {message.watch_out ? <Text style={styles.watchOut}>Watch out: {message.watch_out}</Text> : null}
            {message.next_step ? <Text style={styles.nextStep}>Next: {message.next_step}</Text> : null}
          </View>
        ))}

        {loading ? (
          <View style={[styles.chatBubble, styles.guruBubble]}>
            <Text style={styles.chatRole}>Love Guru</Text>
            <Text style={styles.chatText}>Thinking...</Text>
          </View>
        ) : null}

        {error ? <Text style={styles.notice}>{error}</Text> : null}
      </ScrollView>

      <View style={styles.chatComposer}>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 8, paddingHorizontal: 16, marginBottom: 12 }}>
          {['What should I reply?', 'Make this less dry', 'Flirty but not cringe', 'Ask them out', 'Recover from a bad reply', 'Decode this message'].map(mode => (
             <Pressable key={mode} onPress={() => setDraft(`[${mode}] `)} style={{ paddingHorizontal: 12, paddingVertical: 6, backgroundColor: '#f2eae9', borderRadius: 16 }}>
               <Text style={{ fontSize: 13, color: '#d6346f', fontWeight: '500' }}>{mode}</Text>
             </Pressable>
          ))}
        </ScrollView>
        <View style={{ flexDirection: 'row', alignItems: 'flex-end', gap: 12, paddingHorizontal: 16, paddingBottom: 16 }}>
          <TextInput
            value={draft}
            onChangeText={setDraft}
            placeholder="Ask what to text, say, or do..."
            placeholderTextColor="#958792"
            style={[styles.chatInput, { flex: 1, marginBottom: 0 }]}
            multiline
          />
          <Pressable style={[styles.sendButton, loading && styles.disabled, { marginBottom: 4 }]} onPress={() => send()} disabled={loading}>
            <Ionicons name="send" size={16} color="#ffffff" />
            <Text style={styles.sendButtonText}>Send</Text>
          </Pressable>
        </View>
      </View>
    </View>
  );
}
