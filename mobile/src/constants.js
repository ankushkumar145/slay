export const fallbackQuestions = [
  {
    id: 'warmth',
    section: 'Emotional',
    type: 'slider',
    label: 'How warm do people find you at first?',
    min_label: 'Reserved',
    max_label: 'Instantly warm',
    default: 6,
  },
  {
    id: 'bio_tone',
    section: 'Profile',
    type: 'choice',
    label: 'Pick your profile tone:',
    options: [
      { value: 'witty', label: 'Witty' },
      { value: 'warm', label: 'Warm' },
      { value: 'bold', label: 'Bold' },
      { value: 'mysterious', label: 'Mysterious' },
    ],
  },
];

export const goals = [
  ['better_networking', 'Better networking'],
  ['more_connections', 'More connections'],
  ['meaningful_chats', 'Meaningful chats'],
  ['confidence', 'Confidence'],
];

export const targetMatches = [
  ['industry_leaders', 'Industry leaders'],
  ['long_term_collaborators', 'Long-term collaborators'],
  ['creative_peers', 'Creative peers'],
  ['ambitious_founders', 'Ambitious founders'],
  ['mentors', 'Mentors'],
];

export const exportStyles = [
  ['linkedin', 'LinkedIn'],
  ['twitter', 'Twitter'],
  ['github', 'GitHub'],
  ['personal_site', 'Personal Site'],
];
