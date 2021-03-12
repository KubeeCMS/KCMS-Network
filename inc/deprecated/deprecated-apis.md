# Funções para Migrar

## Classes pata Migrar

* WU_Gutenberg_Support (done)
* WU_Domain_Mapping (done)

* WU_Settings (done)
  * get_sections() (done)
  * get_settings() (done)
  * save_settings() (done)
  * get_setting() (done)
  * save_setting() (done)
  * get_logo() (done)

* WU_Signup (em progresso)
* WU_Gateway (em progresso)

* WU_Error_Reporting (criar tarefa)

## Importante

* WU_Plans (done)
* WU_Util (done)
* WU_Site_Templates (ignored)
* WU_Subscriptions (ignored)
* WU_Webhooks (ignored)
* WU_Site_Hooks (ignored)
* WU_Plans_Limits (ignore)

* WU_Transactions (em progresso)


* WU_Notification
  * Aqui, precisamos encaixar o filtro `apply_filters('wu_days_to_check_expired', 1)` que está sendo usado por muita gente pra mudar quantos dias devemos mandar o email de anúncio de expiração.

* WU_API
  * Checar com o Daniel como está essa parte da API key, para podermos organizar a migração

* WU_Customizer
  * Usar como referência na hora de migrar os settings para os nossos próprios customizers.

* WU_Shortcodes
  * Precisamos migrar os seguintes shortcodes:
    * user_meta (done)
    * paying_users (done)
    * pricing_table
    * plan_link
    * templates_list
    * restricted_content

## Models

* WU_Site (done)
* WU_Site_Template (done)
* WU_Site_Owner (done)
* WU_Broadcast (ignored)
* WU_Coupon (done)
* WU_Plan (done)
* WU_Subscription (done)

## Classes para Deprecar, com alternativa

* WU_Logger (done)
* WU_Links (done)
* WU_Mail (done) - APIs: wu_send_mail
* WU_Page (done)

## Classes para Deprecar, só para não dar fatal

* WU_Multi_Network (done)
* WU_Help_Pointers (done)

## Classes para Ignorar

* WU_Pro_Sites_Support (unsure)
* WU_Widgets (done)

## Revisar

---

# Funções para Migrar

### Signup

* wu_create_html_attributes_from_array
* wu_print_signup_field_option
* wu_print_signup_field_options
* wu_print_signup_field
* wu_create_user
* wu_create_site
* wu_add_signup_step (done)
* wu_add_signup_field (done)

### Models

* wu_get_coupon (done)
* wu_get_plan (done)
* wu_get_plan_by_slug (done)
* wu_get_current_site (done)
* wu_get_site (done)
* wu_get_subscription (done)
* wu_get_subscription_by_integration_key (done)
* wu_get_current_subscription (done)
* wu_is_active_subscriber (done)
* wu_has_plan (done)

### Functions

* wu_get_days_ago (done)
* wu_format_currency (done)

* wu_register_gateway (done)
* wu_get_gateways (done)
* wu_get_gateway (done)
* wu_get_active_gateway (done, deprecated)

* wu_get_interval_string (done)

* wu_is_account_active (ignored)
* wu_get_account_plan (ignored)
* wu_get_offset_timestamp (ignored)
* wu_sanitize_currency_for_saving (ignored)

### Coupon Code

* wu_fix_money_string (js, ignored)
* wu_set_setupfee_value (js, ignored)
* wu_set_yearly_value (js, ignored)
