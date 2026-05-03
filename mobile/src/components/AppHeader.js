import { Pressable, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { styles } from '../theme/styles';

export function AppHeader({ kicker, rightLabel, onRight }) {
  return (
    <View style={styles.topbar}>
      <View>
        <View style={styles.logoMark}>
          <Text style={styles.logo}>SLAY</Text>
          <Ionicons name="heart" size={14} color="#ffffff" />
        </View>
        {kicker ? <Text style={styles.subtitle}>{kicker}</Text> : null}
      </View>
      {rightLabel ? (
        <Pressable style={styles.secondaryButton} onPress={onRight}>
          <Text style={styles.secondaryButtonText}>{rightLabel}</Text>
        </Pressable>
      ) : null}
    </View>
  );
}
