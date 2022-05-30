# sbsedv/request-id-bundle

A Symfony ^6.1 bundle that adds a unique Request-ID.

In the configuration examples shown below, the default values are used.

---

The generic configuration is as follows:

```yaml
# config/packages/sbsedv_request_id.yaml

sbsedv_request_id:
    generator: "SBSEDV\Bundle\RequestIdBundle\Generator\UuidRequestIdGenerator"
    # generator: "SBSEDV\Bundle\RequestIdBundle\Generator\RequestIdGenerator"
    # generator: 'your_custom_service_id' (must implement RequestIdGeneratorInterface)

    outgoing_http_header: "x-request-id" # http header that will be added
    # outgoing_http_header: false # disables the header creation

    incoming_http_header: false # disabled
    # incoming_http_header: "x-request-id" # request header that contains the Request-ID to use
```

---

### **Twig Integration**

If your application has the [symfony/twig-bundle](https://github.com/symfony/twig-bundle) installed, the `request_id` twig function is registered.

```twig
{# templates/example.html.twig #}

<p>Request-ID: {{ request_id() }}</p>
```

You can customize the registered twig function name via:

```yaml
# config/packages/sbsedv_request_id.yaml

sbsedv_request_id:
    twig_function_name: "request_id"
```

This bundle can also provide a generic error page (based on the HtmlErrorRenderer default template) that embeds the request id. This is done by prepending the TwigExtension and setting the `@Twig/Exception/error.html.twig` template.

If you want to disable this functionality, you have to compile the container with the `sbsedv_request_id.twig_error_template` parameter set to `false`.

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
