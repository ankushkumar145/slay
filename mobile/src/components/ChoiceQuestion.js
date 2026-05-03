import { Pressable, Text, View } from 'react-native';

import { styles } from '../theme/styles';

export function ChoiceQuestion({ question, value, onChange }) {
  return (
    <View style={styles.choiceGrid}>
      {question.options.map((option) => (
        <Pressable
          key={option.value}
          style={[styles.choice, value === option.value && styles.activeChoice]}
          onPress={() => onChange(option.value)}
        >
          <Text style={styles.choiceText}>{option.label}</Text>
        </Pressable>
      ))}
    </View>
  );
}
