# Pricing Rule Engine

Symfony / PHP 8.4 task - pricing rules for activity tickets.
no auth, availabilities are not persisted (just come from the form / CLI).

## run locally

needs docker + docker-compose (i used v1, `docker compose` wasn't available) + make.

```bash
make build
make up
make install
```

app should be on http://localhost:8088

if recreate dies with that weird ContainerConfig error: `make down` then `make up` again. happened to me a few times.

seed some demo rules:

```bash
make console ARGS="app:pricing:seed"
```

calculate the brief example from CLI (defaults are already the monday january case):

```bash
make console ARGS="app:pricing:calculate"
```

you should get Adult $90 / Child $45 / total $135.
UI does the same thing under `/`, rules list/create under `/rules`.

other make stuff: `make test`, `make phpstan`, `make cs`, `make shell`, `make console ARGS="..."`.

## what the engine actually does

you give it a booking context (activity + option + activity date + booking date + ticket categories with base prices).
it loads candidate rules from the DB, keeps only the ones whose conditions all match, sorts them, then applies adjustments one after another on each ticket category. result has the final prices + which rules fired (for the UI trail).

conditions right now: activity, option, date range, day of week (ISO 1-7), min advance booking days.
adjustments: percentage discount, fixed discount (cents), percentage surcharge.

activities / options are hardcoded enums. fine for the brief, not a CMS.

## Architecture (rough)

```
src/Pricing/
  Domain/        <- pure php, no symfony. Money, conditions, adjustments, PricingEngine input models
  Application/   <- PricingEngine, PriceCalculator facade, repository interface
  Infrastructure/Doctrine/  <- entity, factory, repository
```

HTTP controllers + forms + CLI sit outside and just call `PriceCalculator`. same path for web and console, which was the point.

### patterns i actually used (not the whole gang of four)

- Strategy / Specification-ish - each condition implements `matches(PricingContext): bool`
- Value Object - `Money` (immutable, cents)
- Factory - Doctrine entity -> domain `PricingRule`
- Repository - `findCandidates()` + `findAllOrdered()`, save on the concrete class
- DIP - application depends on `PricingRuleRepositoryInterface`
- thin Facade - `PriceCalculator` so controllers don't wire engine + repo themselves

i skipped CQRS / event bus / redis / custom DSL. would be overkill here.

## rule ordering

priority DESC, then id ASC when priorities tie.
discounts STACK. so 10% then another 10% on $100 is 100 -> 90 -> 81, not "take the biggest one".
that felt more honest for a "rule engine" than silently picking a winner. if product wanted exclusive rules they'd need a flag - don't have one.

null condition fields mean "any" (e.g. no activity filter).

## Money

USD cents as integers. `$90.00` = `9000`.
no floats in domain math. divide uses half-up.
UI / CLI format with `$` via `Money::format()`.


## persistence / N+1

rules live in one SQLite table `pricing_rule` with flat columns (not a JSON blob of conditions).
`findCandidates()` already narrows in SQL (activity, option, dates, min advance). 
one query -> map to domain rules. no N+1. adding a second table for conditions would need joins / extra queries for basically no gain at this size.

## Concurrency

out of scope. no locking, no optimistic versions on rules. single-writer sqlite demo is fine.
if this went production you'd want at least migrations discipline + maybe row versioning when editing rules (we don't even have edit/delete in the UI per brief).

## trade-offs / things i'd change later

flat columns are easy to query and to put in a form. they suck if you suddenly need nested AND/OR trees - i didn't build AllOf/AnyOf composites even though you could bolt them onto the domain later.
enums instead of DB tables = fast to ship, annoying to extend without a deploy.
no pagination on `/rules` - brief said list+create only.
CSRF is on for forms (symfony default); raw curl posts will fail, browser / WebTestCase is fine.

demo seed also has a museum stacking case and a weekend surcharge so you can poke the calculator a bit beyond the one brief example.

## tests

unit tests for Money, conditions, adjustments, engine (incl. brief example + stacking).
integration for repository + UI flow + CLI commands.
`make test` runs them in docker.

## CI

github actions: composer, migrate test db, phpunit, phpstan, php-cs-fixer dry-run. PHP 8.4.

## time spent

Somewhere around 4 hours for coding? plus a bit earlier creating docker/php 8.4).
