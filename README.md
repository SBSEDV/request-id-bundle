# sbsedv/request-id-bundle

A Symfony ^6.0 bundle that adds a unique Request-ID.

In the configuration examples shown below, the default values are used.

---

The generic configuration is as follows:

```yaml
# config/packages/sbsedv_request_id.yaml

sbsedv_request_id:
    provider: "sbsedv_request_id.provider.default"
    # provider: 'sbsedv_request_id.provider.uuid'
    # provider: 'your_custom_service_id' (must implement Provider\RequestIdProviderInterface)

    outgoing_http_header: "x-request-id" # http header that will be added
    # outgoing_http_header: false # disables the header creation

    incoming_http_header: false # disabled
    # incoming_http_header: "x-request-id" # request header that contains the Request-ID to use

    default_provider:
        length: 16 # How many characters should be used by the default provider
```

---

### **Twig Integration**

If your application has the [symfony/twig-bundle](https://github.com/symfony/twig-bundle) installed, the `request_id` twig function is registered.

```twig
{# templates/example.html.twig #}

<p>Request-ID: {{ request_id() }}</p>
```

This bundle can also provide a generic error page (based on the HtmlErrorRenderer default template) that embeds the request id.

This is used by prepending the TwigExtension and setting the `@TwigBundle/Exception/error.html.twig` template.

```yaml
# config/packages/sbsedv_request_id.yaml

sbsedv_request_id:
    twig_error_template: false
    twig_function_name: "request_id" # you can also customize the registered function name
```

---

### **Monolog Integration**

If your application has the [symfony/monolog-bundle](https://github.com/symfony/monolog-bundle) installed, a log processor is registered that adds the request id to each record.

```yaml
# config/packages/sbsedv_request_id.yaml

sbsedv_request_id:
    monolog_processor:
        key: "request_id" # Key to which the request id will be set
    # monolog_processor: false # disables the processor
```
