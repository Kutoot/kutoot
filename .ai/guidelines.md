# Project Guidelines

## Filament v5
- **Navigation Groups**: When defining a navigation group in a Filament Resource, always use the following type hint to match the base `Resource` class:
  ```php
  protected static string|\UnitEnum|null $navigationGroup = 'Group Name';
  ```

## Seeders
- **String Literals**: Always use regular strings for descriptions. Do not include JSX-style tags or React components like `<CurrencySymbol />` within PHP seeder files. Use proper currency symbols instead (e.g., `₹`).
