# Project Architecture

This system is a multi-application platform composed of several frontend applications and a partially centralized backend.

## Applications

Frontend applications:
- dys
- saha
- sivah
- cavih

Backend:
- handel_api (central API, partially used)

## Real Architecture

Hybrid architecture:
- Frontend: MVP (Model - View - Presenter)
- Backend: Distributed PHP logic + shared API

## Key Insight

Business logic is NOT fully centralized.

Claude must:
- Avoid duplicating logic
- Prefer reuse
- Be careful when modifying shared behavior
