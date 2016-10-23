# ToolsBundle
General Symfony tools used in VisageFour projects

The tools Bundle includes the following:

1. Code
  * Used for generating codes. Can be extended for particular use cases.
  * Currently used in:
  * -- Photocards app (although not implemented with this bundle)
  * -- Slug generation for Twencha EventRegistration app


2. WebHookManager
  * pulls relevant details from entity and passes to the selected URL - currently used for connecting to Zapier

3. Slug entity
  * Used for resolving URLs to relevant objects
  * dependencies: Code Entity

4. Code entity


5. Custom controller extension
  * Has isDevEnvironment(), checkAccess(), getThisPerson() etc

6. BaseFormType
  * has constructor for a form that is used as a service (used as a service asprocessing logic that used to be in gthe controller has been moved to the form).

7. Base Email Register Manager
  * This class is designed to: handle registering of emails and can handle email spooling
  * requires Lexik mailer bundle
  * used in: Anchorcards app and twencha app/bundle
  * The BaseEmailRegisterManager class should be extended into a EmailRegisterManager
  sub class that will hold static variables used to define email layout and template fixtures and set methods to for
  sending each of these emails

8. LexikEmailFixturesGeneric.php
  * This class is designed to make it easier to generate fixtures for Lexik Mailer layout and email template db records
  * used in: Anchorcards app and twencha app/bundle
