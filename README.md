# ToolsBundle
General Symfony tools used in VisageFour projects

The tools Bundle includes the following tools:

1. Code
  * Used for generating codes. Can be extended for particular use cases.
  * Currently used in:
  * -- Photocards app (although not implemented with this bundle)
  * -- Slug generation for Twencha EventRegistration app


2. WebHookManager
  * pulls relevant details from entity and passes to the selected URL - currently used for connecting to Zapier

3. Slug
  * Used for resolving URLs to relevant objects
  * dependencies: Code Entity


