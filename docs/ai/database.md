# Architecture patterns: Database

This document define informations and structures that we use to build database. Follow these rules strictly when suggesting or creating code.

## 1. Migrations

- Migrations on `database/migrations`.
- Should be create `down` method with revert updates from `up` method.
- Should be create index for columns when necessary.
- Should be create foreign keys to keep relationships consistent.
- Use `uuid` for identifier columns
- All date columns should be receive UTC date.

## 2. Models

- Models on `app/Models`.
- Create relationships if have foreign keys.
- Fill the variables `$fillable` and `$casts`.
- Create factory for all models and use Trait `use Illuminate\Database\Eloquent\Factories\HasFactory;`.
- Should not put business rules on models.
- Always use withCount and Eager loaging to avoid N+1 queries.
- Always use soft delete.

## 3. Factories

- Should be create correspondent model.
- Define all columns from `$fillable`.
- Use Facades to create ramdom data from each column.
