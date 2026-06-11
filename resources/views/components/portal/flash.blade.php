{{--
  Global portal flash stack — rendered once in the GVOS shell.

  Handles the two flash keys that no individual portal page renders today:
    • status  — e.g. the post-login "redirected to your dashboard" notice
    • warning — general advisory notices

  `success` and `error` are intentionally NOT rendered here: ~17 portal pages
  already render those locally (often positioned for page context, e.g. above
  the Kanban board). Globalising them would double-render. New pages should use
  <x-portal.alert type="success|error"> for a consistent style.
--}}
@if (session('status'))
    <x-portal.alert type="status">{{ session('status') }}</x-portal.alert>
@endif

@if (session('warning'))
    <x-portal.alert type="warning">{{ session('warning') }}</x-portal.alert>
@endif
