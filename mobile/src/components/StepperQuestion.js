import { Pressable, Text, View } from 'react-native';

import { styles } from '../theme/styles';

export function StepperQuestion({ question, value, onChange }) {
  return (
    <View style={styles.stepperWrap}>
      <View style={styles.stepper}>
        <Pressable style={styles.stepperButton} onPress={() => onChange(Math.max(1, value - 1))}>
          <Text style={styles.stepperButtonText}>-</Text>
        </Pressable>
        <Text style={styles.stepperValue}>{value}/10</Text>
        <Pressable style={styles.stepperButton} onPress={() => onChange(Math.min(10, value + 1))}>
          <Text style={styles.stepperButtonText}>+</Text>
        </Pressable>
      </View>
      <View style={styles.stepperLabels}>
        <Text style={styles.progressText}>{question.min_label}</Text>
        <Text style={styles.progressText}>{question.max_label}</Text>
      </View>
    </View>
  );
}
