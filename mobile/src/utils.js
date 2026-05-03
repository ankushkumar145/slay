export function defaultAnswers(questions) {
  return questions.reduce((allAnswers, question) => {
    allAnswers[question.id] =
      question.type === 'slider' ? question.default ?? 5 : question.options?.[0]?.value;

    return allAnswers;
  }, {});
}

export function quickQuestions(questions) {
  const priority = [
    'social_battery',
    'humor',
    'date_style',
    'warmth',
    'vulnerability',
    'reply_style',
    'bio_tone',
    'green_flag',
  ];

  const byId = new Map(questions.map((question) => [question.id, question]));
  const selected = priority.map((id) => byId.get(id)).filter(Boolean);

  return selected.length >= 6 ? selected : questions.slice(0, 8);
}

export function labelFor(options, value) {
  return options.find(([optionValue]) => optionValue === value)?.[1] ?? value;
}
