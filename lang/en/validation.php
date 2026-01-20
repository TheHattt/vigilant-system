
<?php return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. These messages are written to be neutral, secure,
    | and suitable for enterprise and public-facing applications.
    |
    */

    "accepted" => "The :attribute field must be accepted.",
    "accepted_if" =>
        "The :attribute field must be accepted when :other is :value.",
    "active_url" => "The :attribute field must be a valid URL.",
    "after" => "The :attribute field must be a date after :date.",
    "after_or_equal" =>
        "The :attribute field must be a date after or equal to :date.",
    "alpha" => "The :attribute field must only contain letters.",
    "alpha_dash" =>
        "The :attribute field must only contain letters, numbers, dashes, and underscores.",
    "alpha_num" =>
        "The :attribute field must only contain letters and numbers.",
    "any_of" => "The :attribute field is invalid.",
    "array" => "The :attribute field must be an array.",
    "ascii" => "The :attribute field must only contain valid characters.",
    "before" => "The :attribute field must be a date before :date.",
    "before_or_equal" =>
        "The :attribute field must be a date before or equal to :date.",

    "between" => [
        "array" =>
            "The :attribute field must have between :min and :max items.",
        "file" =>
            "The :attribute field must be between :min and :max kilobytes.",
        "numeric" => "The :attribute field must be between :min and :max.",
        "string" =>
            "The :attribute field must be between :min and :max characters.",
    ],

    "boolean" => "The :attribute field must be true or false.",
    "can" => "The :attribute field contains an unauthorized value.",
    "confirmed" => "The :attribute field confirmation does not match.",
    "contains" => "The :attribute field is missing a required value.",

    /*
    |--------------------------------------------------------------------------
    | Authentication / Security-Sensitive Rules
    |--------------------------------------------------------------------------
    */

    "current_password" => "The provided credentials are invalid.",

    "email" => "The :attribute field must be a valid email address.",
    "enum" => "The selected :attribute is invalid.",
    "exists" => "The selected :attribute is invalid.",

    "unique" => "The provided :attribute is unavailable.",

    /*
    |--------------------------------------------------------------------------
    | Date & Time
    |--------------------------------------------------------------------------
    */

    "date" => "The :attribute field must be a valid date.",
    "date_equals" => "The :attribute field must be a date equal to :date.",
    "date_format" => "The :attribute field must match the format :format.",
    "timezone" => "The :attribute field must be a valid timezone.",

    /*
    |--------------------------------------------------------------------------
    | Numeric & Size Rules
    |--------------------------------------------------------------------------
    */

    "decimal" => "The :attribute field must have :decimal decimal places.",
    "digits" => "The :attribute field must be :digits digits.",
    "digits_between" =>
        "The :attribute field must be between :min and :max digits.",
    "integer" => "The :attribute field must be an integer.",
    "numeric" => "The :attribute field must be a number.",
    "multiple_of" => "The :attribute field must be a multiple of :value.",

    "gt" => [
        "array" => "The :attribute field must have more than :value items.",
        "file" => "The :attribute field must be greater than :value kilobytes.",
        "numeric" => "The :attribute field must be greater than :value.",
        "string" =>
            "The :attribute field must be greater than :value characters.",
    ],

    "gte" => [
        "array" => "The :attribute field must have :value items or more.",
        "file" =>
            "The :attribute field must be greater than or equal to :value kilobytes.",
        "numeric" =>
            "The :attribute field must be greater than or equal to :value.",
        "string" =>
            "The :attribute field must be greater than or equal to :value characters.",
    ],

    "lt" => [
        "array" => "The :attribute field must have less than :value items.",
        "file" => "The :attribute field must be less than :value kilobytes.",
        "numeric" => "The :attribute field must be less than :value.",
        "string" => "The :attribute field must be less than :value characters.",
    ],

    "lte" => [
        "array" => "The :attribute field must not have more than :value items.",
        "file" =>
            "The :attribute field must be less than or equal to :value kilobytes.",
        "numeric" =>
            "The :attribute field must be less than or equal to :value.",
        "string" =>
            "The :attribute field must be less than or equal to :value characters.",
    ],

    "max" => [
        "array" => "The :attribute field must not have more than :max items.",
        "file" =>
            "The :attribute field must not be greater than :max kilobytes.",
        "numeric" => "The :attribute field must not be greater than :max.",
        "string" =>
            "The :attribute field must not be greater than :max characters.",
    ],

    "min" => [
        "array" => "The :attribute field must have at least :min items.",
        "file" => "The :attribute field must be at least :min kilobytes.",
        "numeric" => "The :attribute field must be at least :min.",
        "string" => "The :attribute field must be at least :min characters.",
    ],

    /*
    |--------------------------------------------------------------------------
    | Files & Media
    |--------------------------------------------------------------------------
    */

    "file" => "The :attribute field must be a file.",
    "image" => "The :attribute field must be an image.",
    "mimes" => "The :attribute field must be a file of type: :values.",
    "mimetypes" => "The :attribute field must be a file of type: :values.",
    "uploaded" => "The :attribute failed to upload.",
    "dimensions" => "The :attribute field has invalid image dimensions.",

    /*
    |--------------------------------------------------------------------------
    | Strings
    |--------------------------------------------------------------------------
    */

    "string" => "The :attribute field must be a string.",
    "lowercase" => "The :attribute field must be lowercase.",
    "uppercase" => "The :attribute field must be uppercase.",
    "regex" => "The :attribute field format is invalid.",
    "not_regex" => "The :attribute field format is invalid.",
    "starts_with" =>
        "The :attribute field must start with one of the following: :values.",
    "ends_with" =>
        "The :attribute field must end with one of the following: :values.",

    /*
    |--------------------------------------------------------------------------
    | Password Rules
    |--------------------------------------------------------------------------
    */

    "password" => [
        "letters" => "The :attribute field must contain at least one letter.",
        "mixed" =>
            "The :attribute field must contain both uppercase and lowercase letters.",
        "numbers" => "The :attribute field must contain at least one number.",
        "symbols" => "The :attribute field must contain at least one symbol.",
        "uncompromised" =>
            "The chosen :attribute does not meet security requirements.",
    ],

    /*
    |--------------------------------------------------------------------------
    | Presence & Requirement
    |--------------------------------------------------------------------------
    */

    "required" => "The :attribute field is required.",
    "present" => "The :attribute field must be present.",
    "filled" => "The :attribute field must have a value.",

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    "custom" => [
        // 'email.required' => 'Please enter your email address.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Use this section to make attribute names more user-friendly.
    | Example: 'email' => 'Email address'
    |
    */

    "attributes" => [],
];
