# printService

Cloud print queue service + Windows print agent specification project.

## Included artifacts

- `PRINTSERVICE_TECHNICAL_SPEC.md` - full technical specification (TZ)
- `OPENAPI_PRINTSERVICE_V1.yaml` - API contract (OpenAPI 3.0)
- `DB_SCHEMA_MVP.sql` - MySQL schema for MVP
- `AGENT_PROTOCOL.md` - Windows agent protocol and runtime behavior

## Next implementation steps

1. Bootstrap Laravel API project structure.
2. Implement migrations/models from `DB_SCHEMA_MVP.sql`.
3. Implement endpoints from `OPENAPI_PRINTSERVICE_V1.yaml`.
4. Build Windows x86 agent according to `AGENT_PROTOCOL.md`.
