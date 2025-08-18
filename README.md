# Yeashy Compliance

[![Latest Version on Packagist](https://img.shields.io/packagist/v/yeashy/compliance.svg?style=flat-square)](https://packagist.org/packages/yeashy/compliance)
[![Total Downloads](https://img.shields.io/packagist/dt/yeashy/compliance.svg?style=flat-square)](https://packagist.org/packages/yeashy/compliance)
[![License](https://img.shields.io/packagist/l/yeashy/compliance.svg?style=flat-square)](https://github.com/yeashy/compliance/blob/main/LICENSE)

**Yeashy Compliance** is an elegant pipeline-based validation system for Laravel, designed to extend `FormRequest` validation with domain-driven, reusable, and expressive rule objects.

---

## 🚀 Features

- ✅ Seamless integration with Laravel `FormRequest`
- 🧩 Pipeline-based rule processing
- 🎯 HTTP-method-specific rule handling (`GET`, `POST`, etc.)
- 🔁 Composable and testable rule classes
- 📦 Fully compatible with standard Laravel validation

---

## 📦 Installation

Install the package via Composer:

```bash
composer require yeashy/compliance
```

No additional service provider registration is required.

## 🧑‍💻 Getting Started

To use Compliance in your requests, simply extend the base `ComplianceRequest` instead of Laravel’s `FormRequest`.

### Step 1: Create a Compliance Rule

A compliance rule is a class that extends `Yeashy\Compliance\Rules\ComplianceRule` and implements the `validate()` method.

```PHP
use Yeashy\Compliance\Rules\ComplianceRule;

class EmailIsNotBlocked extends ComplianceRule
{
    protected string $key = 'email';
    protected string $message = 'This email address is blocked.';

    protected function validate(): void
    {
        if ($this->data->email === 'blocked@example.com') {
            $this->invalidateAndExit(); // Add error and stop the pipeline
        }
    }
}
```
### Step 2: Apply the Rule in a Request

Extend `ComplianceRequest` and define the rules in the `$compliances` property, or specify them per HTTP method (`$postCompliances`, `$getCompliances`, etc.).

```PHP
use Yeashy\Compliance\Requests\ComplianceRequest;

class RegisterRequest extends ComplianceRequest
{
    protected array $postCompliances = [
        \App\Compliance\Rules\EmailIsNotBlocked::class,
    ];

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }
}
```

That’s it! The request will now go through both Laravel’s standard validation and your custom compliance pipeline.

## ⚙️ How Compliance Rules Work

Each rule receives an object containing:
- All input data from the request `(Request::all())`
- Route parameters

The `validate()` method inside each rule is executed in sequence. You can:
- Use `invalidate($key, $message)` to add an error and continue to the next rule.
- Use `invalidateAndExit()` to add an error and stop the pipeline immediately.
- Use `skipNext()` to stop the pipeline without adding an error (optional shortcut).

### HTTP Method Specific Rules

Compliance rules can be defined globally for all methods:
```PHP
protected array $compliances = [...];`
```
Or scoped to specific HTTP verbs:
```PHP
protected array $postCompliances = [...];
protected array $getCompliances = [...];
// Supported: get, post, put, patch, delete, head, options
```
### Error Merging

Compliance errors are automatically merged with Laravel’s validation errors and returned in the standard format.
If a compliance rule sets a custom `$statusCode`, it will override the default HTTP 422 status.

## ✅ Best Practices
- Rules should follow the Single Responsibility Principle: one rule — one concern.
- Use meaningful keys (`$key`) to ensure correct field mapping in error responses.
- Localize messages using `__('...')` whenever possible.
- Prefer `invalidateAndExit()` for critical rules (e.g., credential checks).
- Use `skipNext()` if the current rule makes the remaining ones irrelevant.

## 📂 Example

Rule: Ensure the user is not blocked

```PHP
use Illuminate\Support\Facades\Auth;
use Yeashy\Compliance\Rules\ComplianceRule;

class UserIsNotBlocked extends ComplianceRule
{
    protected string $key = 'user';
    protected string $message = 'Your account has been blocked.';
    public int $statusCode = 403;

    protected function validate(): void
    {
        $user = Auth::user();

        if (! $user || $user->is_blocked) {
            $this->invalidateAndExit();
        }
    }
}

```

### Usage in Request:
```PHP
use Yeashy\Compliance\Requests\ComplianceRequest;

class DashboardRequest extends ComplianceRequest
{
    protected array $compliances = [
        \App\Compliance\Rules\UserIsNotBlocked::class,
        \App\Compliance\Rules\AccountIsVerified::class,
    ];

    protected array $getCompliances = [
        \App\Compliance\Rules\SubscriptionIsActive::class,
        \App\Compliance\Rules\UserHasAccessToResource::class,
    ];

    public function rules(): array
    {
        return [
            'resource_id' => ['required', 'uuid'],
        ];
    }
}
```

In this example:
- `$compliances` defines global rules that apply to all HTTP methods.
- `$getCompliances` defines additional rules applied only for `GET` requests.
- All rules are processed in order, and execution stops if a rule uses `invalidateAndExit()`.

```
## 🧪 Testing

You can test compliance rules in isolation:
```PHP
public function test_email_is_not_blocked()
{
    $rule = new EmailIsNotBlocked();

    $data = (object) ['email' => 'blocked@example.com'];

    $rule->handle($data, fn($data) => $data);

    $this->assertNotEmpty($data->__errorMessages);
}
```
## 📄 License

The MIT License (MIT). Please see License File for more information.

## 🤝 Contributing

Contributions, issues and feature requests are welcome!
Feel free to submit a PR or open an issue.
