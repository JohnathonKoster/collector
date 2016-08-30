# Collector

## Tests

The `collector` tool intentionally uses the same versions of `mockery/mockery` and `phpunit/phpunit` as the generated output. As of now, these versions are:

```json
"require-dev": {
    "mockery/mockery": "~0.9.4",
	"phpunit/phpunit": "~4.1"
}
```

This is done so that the tool does not have to do a `composer install` for *every* generated output to run the tests.