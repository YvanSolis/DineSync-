<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class GovernmentDiscountService
{
    public const TYPE_NONE = 'none';
    public const TYPE_SENIOR = 'senior';
    public const TYPE_PWD = 'pwd';

    private const VAT_RATE = 0.12;
    private const DISCOUNT_RATE = 0.20;

    public function calculate(
        float|int|string $originalAmount,
        string $discountType,
        int $qualifiedDiners = 0,
        int $totalDiners = 0
    ): array {
        $discountType = strtolower(trim($discountType));

        $this->validateDiscountType($discountType);

        $originalCentavos = $this->toCentavos($originalAmount);

        if ($originalCentavos < 0) {
            throw ValidationException::withMessages([
                'original_amount' => 'The order total cannot be negative.',
            ]);
        }

        if ($discountType === self::TYPE_NONE) {
            return $this->buildNoDiscountResult(
                $originalCentavos,
                $totalDiners
            );
        }

        $this->validateDiners(
            $qualifiedDiners,
            $totalDiners
        );

        $qualifiedGrossCentavos = (int) round(
            $originalCentavos
            * ($qualifiedDiners / $totalDiners),
            0,
            PHP_ROUND_HALF_UP
        );

        $qualifiedGrossCentavos = min(
            $qualifiedGrossCentavos,
            $originalCentavos
        );

        $regularGrossCentavos =
            $originalCentavos - $qualifiedGrossCentavos;

        $qualifiedVatExclusiveCentavos = (int) round(
            $qualifiedGrossCentavos / (1 + self::VAT_RATE),
            0,
            PHP_ROUND_HALF_UP
        );

        $vatExemptAmountCentavos =
            $qualifiedGrossCentavos
            - $qualifiedVatExclusiveCentavos;

        $discountAmountCentavos = (int) round(
            $qualifiedVatExclusiveCentavos
            * self::DISCOUNT_RATE,
            0,
            PHP_ROUND_HALF_UP
        );

        $qualifiedPayableCentavos =
            $qualifiedVatExclusiveCentavos
            - $discountAmountCentavos;

        $finalAmountCentavos =
            $regularGrossCentavos
            + $qualifiedPayableCentavos;

        $totalBenefitCentavos =
            $originalCentavos - $finalAmountCentavos;

        return [
            'discount_type' => $discountType,
            'discount_rate' => self::DISCOUNT_RATE,
            'vat_rate' => self::VAT_RATE,

            'qualified_diners' => $qualifiedDiners,
            'total_diners' => $totalDiners,
            'qualified_ratio' => round(
                $qualifiedDiners / $totalDiners,
                6
            ),

            'original_amount' =>
                $this->fromCentavos($originalCentavos),

            'qualified_gross_amount' =>
                $this->fromCentavos($qualifiedGrossCentavos),

            'regular_gross_amount' =>
                $this->fromCentavos($regularGrossCentavos),

            'qualified_vat_exclusive_amount' =>
                $this->fromCentavos(
                    $qualifiedVatExclusiveCentavos
                ),

            'vat_exempt_amount' =>
                $this->fromCentavos(
                    $vatExemptAmountCentavos
                ),

            'discount_amount' =>
                $this->fromCentavos(
                    $discountAmountCentavos
                ),

            'qualified_payable_amount' =>
                $this->fromCentavos(
                    $qualifiedPayableCentavos
                ),

            'total_benefit_amount' =>
                $this->fromCentavos(
                    $totalBenefitCentavos
                ),

            'final_amount' =>
                $this->fromCentavos(
                    $finalAmountCentavos
                ),
        ];
    }

    private function validateDiscountType(
        string $discountType
    ): void {
        if (!in_array($discountType, [
            self::TYPE_NONE,
            self::TYPE_SENIOR,
            self::TYPE_PWD,
        ], true)) {
            throw ValidationException::withMessages([
                'discount_type' =>
                    'The selected discount type is invalid.',
            ]);
        }
    }

    private function validateDiners(
        int $qualifiedDiners,
        int $totalDiners
    ): void {
        if ($totalDiners < 1) {
            throw ValidationException::withMessages([
                'total_diners' =>
                    'The total number of diners must be at least 1.',
            ]);
        }

        if ($qualifiedDiners < 1) {
            throw ValidationException::withMessages([
                'qualified_diners' =>
                    'At least one qualified diner is required.',
            ]);
        }

        if ($qualifiedDiners > $totalDiners) {
            throw ValidationException::withMessages([
                'qualified_diners' =>
                    'Qualified diners cannot exceed total diners.',
            ]);
        }
    }

    private function buildNoDiscountResult(
        int $originalCentavos,
        int $totalDiners
    ): array {
        return [
            'discount_type' => self::TYPE_NONE,
            'discount_rate' => 0.00,
            'vat_rate' => self::VAT_RATE,

            'qualified_diners' => 0,
            'total_diners' => max($totalDiners, 0),
            'qualified_ratio' => 0.00,

            'original_amount' =>
                $this->fromCentavos($originalCentavos),

            'qualified_gross_amount' => 0.00,
            'regular_gross_amount' =>
                $this->fromCentavos($originalCentavos),

            'qualified_vat_exclusive_amount' => 0.00,
            'vat_exempt_amount' => 0.00,
            'discount_amount' => 0.00,
            'qualified_payable_amount' => 0.00,
            'total_benefit_amount' => 0.00,

            'final_amount' =>
                $this->fromCentavos($originalCentavos),
        ];
    }

    private function toCentavos(
        float|int|string $amount
    ): int {
        if (!is_numeric($amount)) {
            throw ValidationException::withMessages([
                'original_amount' =>
                    'The order total must be a valid number.',
            ]);
        }

        return (int) round(
            ((float) $amount) * 100,
            0,
            PHP_ROUND_HALF_UP
        );
    }

    private function fromCentavos(
        int $centavos
    ): float {
        return round($centavos / 100, 2);
    }
}
