import { ChoiceQuestion } from './ChoiceQuestion';
import { StepperQuestion } from './StepperQuestion';

export function QuestionRenderer({ question, value, onChange }) {
  if (question.type === 'slider') {
    return (
      <StepperQuestion
        question={question}
        value={value ?? question.default ?? 5}
        onChange={onChange}
      />
    );
  }

  return <ChoiceQuestion question={question} value={value} onChange={onChange} />;
}
