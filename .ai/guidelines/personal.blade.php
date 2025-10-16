## Personal Rules

A concise set of conventions and preferences designed to keep your codebase clean, consistent, and maintainable.

### Facades over Helpers

Always use **Facades** instead of helpers. Helpers often cause confusion and reduce clarity.

### Authorization

- Use `Gate::authorize()` instead of `$this->authorize()` for more explicit and consistent authorization handling.

### Validation

- Prefer **Form Request Validation** over inline validation within controllers. This keeps controllers clean and ensures proper separation of concerns.

@verbatim
<code-snippet name="Generate Form Request" lang="bash">
    php artisan make:request StorePostRequest
</code-snippet>
@endverbatim

- Use **array syntax** for validation rules to improve readability and maintain consistency:

@verbatim
<code-snippet name="Form Request Validation Rules" lang="php">
public function rules(): array
{
    return [
        'title' => ['required', 'unique:posts', 'max:255'],
        'body' => ['required'],
    ];
}
</code-snippet>
@endverbatim

### Frontend Class Merging

- Use the `cn()` helper from `@/lib/utils` for merging class names. It combines `clsx` and `tailwind-merge` under the hood, ensuring clean and conflict-free class management.

### PHP Enums

- Always use the `HasNameValue` trait to maintain readability and consistency.

### Avoid `isset()`

- Do not use `isset()` in PHP. Use the **null coalescing operator (`??`)** for a cleaner and more expressive approach.

### Controller Preferences

- Prefer **Resource Controllers**, even if only two methods are used. They provide structure and consistency.
- Use **Single Action Controllers** for complex logic to maintain focus and clarity.

@verbatim
<code-snippet name="Single Action Controller Example" lang="php">
class ProvisionServerController extends Controller
{
    public function __invoke()
    {
        // ...
    }
}
</code-snippet>
@endverbatim

- Favor creating **additional controllers** over adding non-resource methods within existing ones. This keeps responsibilities well-defined and code easier to maintain. E.g. `TeamController@switch` could be a `SwitchTeamController`.
