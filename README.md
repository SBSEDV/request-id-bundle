[![PHPStan](https://github.com/SBSEDV/request-id-bundle/actions/workflows/phpstan.yml/badge.svg)](https://github.com/SBSEDV/request-id-bundle/actions/workflows/phpstan.yml)
[![PHPCS-Fixer](https://github.com/SBSEDV/request-id-bundle/actions/workflows/phpcsfixer.yml/badge.svg)](https://github.com/SBSEDV/request-id-bundle/actions/workflows/phpcsfixer.yml)

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

---

### **Error Renderer**

By default this bundle decorates the `error_renderer` service and inserts the current request id before the `</body>` tag.

```yaml
# config/packages/sbsedv_request_id.yaml

sbsedv_request_id:
    error_renderer_decorator: false # enabled by default
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
