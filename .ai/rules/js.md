---
paths:
  - 'resources/js/**'
---

# Js

## Prevent duplicate keys in Svelte 5 keyed each blocks
In Svelte 5 keyed each blocks ({#each items as item (key)}), duplicate keys throw an uncaught runtime error that prevents layout and component trees from mounting. In Breadcrumbs or navigation components, ensure keys are unique by appending the loop index: (`${item.href}-${index}`).
