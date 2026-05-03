import { Text, View } from 'react-native';

import { styles } from '../theme/styles';

export function ResultList({ title, items = [] }) {
  return (
    <View style={styles.card}>
      <Text style={styles.sectionTitle}>{title}</Text>
      {items.map((item) => {
        const label = typeof item === 'string' ? null : item.label ?? item.prompt;
        const value = typeof item === 'string' ? item : item.value ?? item.answer;

        return (
          <View style={styles.listItem} key={`${label ?? value}`}>
            {label ? <Text style={styles.cardLabel}>{label}</Text> : null}
            <Text style={styles.cardText}>{value}</Text>
          </View>
        );
      })}
    </View>
  );
}
