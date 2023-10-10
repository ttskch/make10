<?php

declare(strict_types=1);

final readonly class Operand
{
    public function __construct(
        public float $value,
        public string $expression,
    ) {
    }
}

final readonly class Operation
{
    public function __construct(
        private string $operator,
    ) {
        if (!in_array($operator, ['+', '-', '*', '/'], true)) {
            throw new InvalidArgumentException();
        }
    }

    public function __invoke(float $a, float $b): float
    {
        if ($this->operator === '/' && $b == 0) {
            throw new InvalidArgumentException();
        }

        return match ($this->operator) {
            '+' => $a + $b,
            '-' => $a - $b,
            '*' => $a * $b,
            '/' => $a / $b,
        };
    }

    public function __toString(): string
    {
        return $this->operator;
    }
}

/**
 * @param array<Operand> $operands
 */
function solve(array $operands, int|float $answer): ?string
{
    $nextTwoOperandsCombinations = [];
    for ($i = 0; $i < count($operands); $i++) {
        for ($j = 0; $j < count($operands); $j++) {
            if ($i !== $j) {
                $nextTwoOperandsCombinations[] = [$operands[$i], $operands[$j]];
            }
        }
    }

    foreach ($nextTwoOperandsCombinations as [$operand1, $operand2]) {

        $operations = [
            new Operation('+'),
            new Operation('-'),
            new Operation('*'),
            new Operation('/'),
        ];

        foreach ($operations as $operation) {
            if (strval($operation) === '/' && $operand2->value == 0) {
                continue;
            }
            $value = $operation($operand1->value, $operand2->value);
            $expression = sprintf('(%s %s %s)', $operand1->expression, $operation, $operand2->expression);

            $restOperands = [];
            foreach ($operands as $operand) {
                if ($operand !== $operand1 && $operand !== $operand2) {
                    $restOperands[] = $operand;
                }
            }

            if (!$restOperands) {
                if (isEqual($value, $answer)) {
                    return $expression;
                } else {
                    continue;
                }
            }

            $restOperands[] = new Operand($value, $expression);

            if ($solved = solve($restOperands, $answer)) {
                return $solved;
            }
        }
    }

    return null;
}

function isEqual(int|float $a, int|float $b): bool
{
    return abs($a - $b) < 0.0001;
}

$inputs = array_map('intval', array_slice($argv, 1));
$answer = array_pop($inputs);
$rawOperands = $inputs;
echo sprintf("Make %d from %s\n", $answer, implode(', ', $rawOperands));
$operands = array_map(fn (int $operand) => new Operand($operand, strval($operand)), $rawOperands);
echo sprintf("%s\n", solve($operands, $answer) ?? 'No solution');
