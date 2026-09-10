import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\InstitutionController::index
 * @see app/Http/Controllers/Admin/InstitutionController.php:23
 * @route '/admin/institutions'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/institutions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\InstitutionController::index
 * @see app/Http/Controllers/Admin/InstitutionController.php:23
 * @route '/admin/institutions'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InstitutionController::index
 * @see app/Http/Controllers/Admin/InstitutionController.php:23
 * @route '/admin/institutions'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\InstitutionController::index
 * @see app/Http/Controllers/Admin/InstitutionController.php:23
 * @route '/admin/institutions'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\InstitutionController::index
 * @see app/Http/Controllers/Admin/InstitutionController.php:23
 * @route '/admin/institutions'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\InstitutionController::index
 * @see app/Http/Controllers/Admin/InstitutionController.php:23
 * @route '/admin/institutions'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\InstitutionController::index
 * @see app/Http/Controllers/Admin/InstitutionController.php:23
 * @route '/admin/institutions'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\Admin\InstitutionController::create
 * @see app/Http/Controllers/Admin/InstitutionController.php:63
 * @route '/admin/institutions/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/institutions/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\InstitutionController::create
 * @see app/Http/Controllers/Admin/InstitutionController.php:63
 * @route '/admin/institutions/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InstitutionController::create
 * @see app/Http/Controllers/Admin/InstitutionController.php:63
 * @route '/admin/institutions/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\InstitutionController::create
 * @see app/Http/Controllers/Admin/InstitutionController.php:63
 * @route '/admin/institutions/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\InstitutionController::create
 * @see app/Http/Controllers/Admin/InstitutionController.php:63
 * @route '/admin/institutions/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\InstitutionController::create
 * @see app/Http/Controllers/Admin/InstitutionController.php:63
 * @route '/admin/institutions/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\InstitutionController::create
 * @see app/Http/Controllers/Admin/InstitutionController.php:63
 * @route '/admin/institutions/create'
 */
        createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
/**
* @see \App\Http\Controllers\Admin\InstitutionController::store
 * @see app/Http/Controllers/Admin/InstitutionController.php:82
 * @route '/admin/institutions'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/institutions',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\InstitutionController::store
 * @see app/Http/Controllers/Admin/InstitutionController.php:82
 * @route '/admin/institutions'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InstitutionController::store
 * @see app/Http/Controllers/Admin/InstitutionController.php:82
 * @route '/admin/institutions'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\InstitutionController::store
 * @see app/Http/Controllers/Admin/InstitutionController.php:82
 * @route '/admin/institutions'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InstitutionController::store
 * @see app/Http/Controllers/Admin/InstitutionController.php:82
 * @route '/admin/institutions'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\InstitutionController::show
 * @see app/Http/Controllers/Admin/InstitutionController.php:0
 * @route '/admin/institutions/{institution}'
 */
export const show = (args: { institution: string | number } | [institution: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/institutions/{institution}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\InstitutionController::show
 * @see app/Http/Controllers/Admin/InstitutionController.php:0
 * @route '/admin/institutions/{institution}'
 */
show.url = (args: { institution: string | number } | [institution: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { institution: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    institution: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        institution: args.institution,
                }

    return show.definition.url
            .replace('{institution}', parsedArgs.institution.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InstitutionController::show
 * @see app/Http/Controllers/Admin/InstitutionController.php:0
 * @route '/admin/institutions/{institution}'
 */
show.get = (args: { institution: string | number } | [institution: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\InstitutionController::show
 * @see app/Http/Controllers/Admin/InstitutionController.php:0
 * @route '/admin/institutions/{institution}'
 */
show.head = (args: { institution: string | number } | [institution: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\InstitutionController::show
 * @see app/Http/Controllers/Admin/InstitutionController.php:0
 * @route '/admin/institutions/{institution}'
 */
    const showForm = (args: { institution: string | number } | [institution: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\InstitutionController::show
 * @see app/Http/Controllers/Admin/InstitutionController.php:0
 * @route '/admin/institutions/{institution}'
 */
        showForm.get = (args: { institution: string | number } | [institution: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\InstitutionController::show
 * @see app/Http/Controllers/Admin/InstitutionController.php:0
 * @route '/admin/institutions/{institution}'
 */
        showForm.head = (args: { institution: string | number } | [institution: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Admin\InstitutionController::edit
 * @see app/Http/Controllers/Admin/InstitutionController.php:110
 * @route '/admin/institutions/{institution}/edit'
 */
export const edit = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/institutions/{institution}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\InstitutionController::edit
 * @see app/Http/Controllers/Admin/InstitutionController.php:110
 * @route '/admin/institutions/{institution}/edit'
 */
edit.url = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { institution: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { institution: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    institution: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        institution: typeof args.institution === 'object'
                ? args.institution.id
                : args.institution,
                }

    return edit.definition.url
            .replace('{institution}', parsedArgs.institution.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InstitutionController::edit
 * @see app/Http/Controllers/Admin/InstitutionController.php:110
 * @route '/admin/institutions/{institution}/edit'
 */
edit.get = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\InstitutionController::edit
 * @see app/Http/Controllers/Admin/InstitutionController.php:110
 * @route '/admin/institutions/{institution}/edit'
 */
edit.head = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\InstitutionController::edit
 * @see app/Http/Controllers/Admin/InstitutionController.php:110
 * @route '/admin/institutions/{institution}/edit'
 */
    const editForm = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\InstitutionController::edit
 * @see app/Http/Controllers/Admin/InstitutionController.php:110
 * @route '/admin/institutions/{institution}/edit'
 */
        editForm.get = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\InstitutionController::edit
 * @see app/Http/Controllers/Admin/InstitutionController.php:110
 * @route '/admin/institutions/{institution}/edit'
 */
        editForm.head = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit.form = editForm
/**
* @see \App\Http\Controllers\Admin\InstitutionController::update
 * @see app/Http/Controllers/Admin/InstitutionController.php:134
 * @route '/admin/institutions/{institution}'
 */
export const update = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/institutions/{institution}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\InstitutionController::update
 * @see app/Http/Controllers/Admin/InstitutionController.php:134
 * @route '/admin/institutions/{institution}'
 */
update.url = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { institution: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { institution: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    institution: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        institution: typeof args.institution === 'object'
                ? args.institution.id
                : args.institution,
                }

    return update.definition.url
            .replace('{institution}', parsedArgs.institution.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InstitutionController::update
 * @see app/Http/Controllers/Admin/InstitutionController.php:134
 * @route '/admin/institutions/{institution}'
 */
update.put = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\InstitutionController::update
 * @see app/Http/Controllers/Admin/InstitutionController.php:134
 * @route '/admin/institutions/{institution}'
 */
update.patch = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\InstitutionController::update
 * @see app/Http/Controllers/Admin/InstitutionController.php:134
 * @route '/admin/institutions/{institution}'
 */
    const updateForm = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InstitutionController::update
 * @see app/Http/Controllers/Admin/InstitutionController.php:134
 * @route '/admin/institutions/{institution}'
 */
        updateForm.put = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\InstitutionController::update
 * @see app/Http/Controllers/Admin/InstitutionController.php:134
 * @route '/admin/institutions/{institution}'
 */
        updateForm.patch = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Admin\InstitutionController::destroy
 * @see app/Http/Controllers/Admin/InstitutionController.php:164
 * @route '/admin/institutions/{institution}'
 */
export const destroy = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/institutions/{institution}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\InstitutionController::destroy
 * @see app/Http/Controllers/Admin/InstitutionController.php:164
 * @route '/admin/institutions/{institution}'
 */
destroy.url = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { institution: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { institution: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    institution: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        institution: typeof args.institution === 'object'
                ? args.institution.id
                : args.institution,
                }

    return destroy.definition.url
            .replace('{institution}', parsedArgs.institution.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InstitutionController::destroy
 * @see app/Http/Controllers/Admin/InstitutionController.php:164
 * @route '/admin/institutions/{institution}'
 */
destroy.delete = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\InstitutionController::destroy
 * @see app/Http/Controllers/Admin/InstitutionController.php:164
 * @route '/admin/institutions/{institution}'
 */
    const destroyForm = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InstitutionController::destroy
 * @see app/Http/Controllers/Admin/InstitutionController.php:164
 * @route '/admin/institutions/{institution}'
 */
        destroyForm.delete = (args: { institution: string | { id: string } } | [institution: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const institutions = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
show: Object.assign(show, show),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default institutions