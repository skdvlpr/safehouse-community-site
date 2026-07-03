<?php

return [

    'base_url' => env('ESPOCRM_BASE_URL', 'https://nonprofit-espocrm.ddev.site'),

    'api_key' => env('ESPOCRM_API_KEY'),

    /*
     * EspoCRM User id for assignedUserId on PrimaNota POST.
     */
    'assigned_user_id' => env('ESPOCRM_ASSIGNED_USER_ID'),

    'finanziamento' => [
        'entity' => 'Opportunity',
        'default_stage' => env('ESPOCRM_FINANZIAMENTO_STAGE', 'Fundraising'),
        'default_close_date' => env('ESPOCRM_FINANZIAMENTO_CLOSE_DATE', '2026-12-31'),
        'default_probability' => (int) env('ESPOCRM_FINANZIAMENTO_PROBABILITY', 60),
        // Espo Opportunity.amount is required on create (campaign goal placeholder).
        'default_amount' => (float) env('ESPOCRM_FINANZIAMENTO_DEFAULT_AMOUNT', 0),
        'default_currency' => env('ESPOCRM_FINANZIAMENTO_DEFAULT_CURRENCY', 'EUR'),
    ],

    'prima_nota' => [
        'entity' => 'PrimaNota',
        'entry_type' => 'Income',
        'internal_classification' => 'Donation',
        'default_subject_name' => env('ESPOCRM_PRIMA_NOTA_DEFAULT_SUBJECT', 'Donatore'),
        'default_beneficiary_name' => env('ESPOCRM_PRIMA_NOTA_DEFAULT_BENEFICIARY', 'Safe House'),
        'beneficiary_party_entity' => 'Account',
    ],

    'reporting' => [
        'meal_count_summary_path' => 'NonprofitEspocrm/reporting/meal-count/summary',
        'association_meal_count_summary_path' => 'NonprofitEspocrm/reporting/association-meal-count/summary',
        'meal_count_totals_path' => 'NonprofitEspocrm/reporting/meal-count/totals',
        'association_meal_count_totals_path' => 'NonprofitEspocrm/reporting/association-meal-count/totals',
        'intervention_totals_path' => 'NonprofitEspocrm/reporting/intervention/totals',
    ],

];
