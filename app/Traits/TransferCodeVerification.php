<?php

namespace App\Traits;

use Session;

trait TransferCodeVerification
{
    /**
     * Ordered list of the verification steps that can be required before a
     * transfer is processed. Each entry holds the session flag that records
     * that the step was completed and the route that starts the step.
     *
     * @return array<string, string[]>
     */
    protected function verificationSteps($settings)
    {
        $steps = [];

        if ($settings->code1status == 1) {
            $steps['code1'] = ['flag' => 'verif_code1', 'route' => 'code1verification'];
        }
        if ($settings->code2status == 1) {
            $steps['code2'] = ['flag' => 'verif_code2', 'route' => 'verificationcode2'];
        }
        if ($settings->code3status == 1) {
            $steps['code3'] = ['flag' => 'verif_code3', 'route' => 'verification3code'];
        }
        if ($settings->code4status == 1) {
            $steps['code4'] = ['flag' => 'verif_code4', 'route' => 'code4verification'];
        }
        if ($settings->code5status == 1) {
            $steps['code5'] = ['flag' => 'verif_code5', 'route' => 'code5verification'];
        }
        if (!empty($settings->tax_code_status) && $settings->tax_code_status == 1) {
            $steps['tax_code'] = ['flag' => 'verif_tax_code', 'route' => 'taxcodeverification'];
        }
        if ($settings->otp == 1) {
            $steps['otp'] = ['flag' => 'verif_otp', 'route' => 'getotp'];
        }

        return $steps;
    }

    /**
     * Whether any verification step is currently enabled in the settings.
     */
    protected function hasVerificationSteps($settings)
    {
        return count($this->verificationSteps($settings)) > 0;
    }

    /**
     * The route of the first enabled verification step that has not been
     * completed yet, or null when every enabled step has been completed.
     *
     * @return string|null
     */
    protected function firstPendingVerification($settings)
    {
        foreach ($this->verificationSteps($settings) as $step) {
            if (!Session::get($step['flag'])) {
                return $step['route'];
            }
        }
        return null;
    }

    /**
     * Clear the verification flags for a transfer. Called whenever a new
     * transfer is started so a previously completed verification cannot be
     * reused for a different transaction.
     */
    protected function resetVerificationFlags()
    {
        foreach (['verif_code1', 'verif_code2', 'verif_code3', 'verif_code4', 'verif_code5', 'verif_tax_code', 'verif_otp'] as $flag) {
            Session::forget($flag);
        }
    }
}