import { Pressable, Text, View } from 'react-native';
import { Ionicons, MaterialCommunityIcons } from '@expo/vector-icons';

import { styles } from '../theme/styles';

const tabs = [
  { key: 'dashboard', label: 'Home', icon: 'home-outline', family: Ionicons },
  { key: 'guru', label: 'Chat', icon: 'chat-heart-outline', family: MaterialCommunityIcons },
  { key: 'quiz', label: 'Analyze', icon: 'sparkles-outline', family: Ionicons },
  { key: 'profile', label: 'Me', icon: 'person-outline', family: Ionicons },
];

export function BottomTabs({ active, onSelect }) {
  return (
    <View style={styles.bottomTabs}>
      {tabs.map((tab) => {
        const isActive = active === tab.key;
        const isPrimary = tab.key === 'quiz';
        const iconColor = isActive ? '#ffffff' : isPrimary ? '#d6346f' : '#74645c';
        const Icon = tab.family;

        return (
          <Pressable
            key={tab.key}
            style={[
              styles.bottomTab,
              isPrimary && styles.primaryBottomTab,
              isActive && styles.activeBottomTab,
              isPrimary && isActive && styles.activePrimaryBottomTab,
            ]}
            onPress={() => onSelect(tab.key)}
          >
            <Icon name={tab.icon} size={isPrimary ? 21 : 18} color={iconColor} />
            <Text
              style={[
                styles.bottomTabLabel,
                isPrimary && styles.primaryBottomTabLabel,
                isActive && styles.activeBottomTabText,
              ]}
            >
              {tab.label}
            </Text>
          </Pressable>
        );
      })}
    </View>
  );
}
