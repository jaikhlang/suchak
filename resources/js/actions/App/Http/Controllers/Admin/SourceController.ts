import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\SourceController::crawlNow
 * @see app/Http/Controllers/Admin/SourceController.php:194
 * @route '/admin/sources/{source}/crawl'
 */
export const crawlNow = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: crawlNow.url(args, options),
    method: 'post',
})

crawlNow.definition = {
    methods: ["post"],
    url: '/admin/sources/{source}/crawl',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SourceController::crawlNow
 * @see app/Http/Controllers/Admin/SourceController.php:194
 * @route '/admin/sources/{source}/crawl'
 */
crawlNow.url = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { source: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { source: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    source: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        source: typeof args.source === 'object'
                ? args.source.id
                : args.source,
                }

    return crawlNow.definition.url
            .replace('{source}', parsedArgs.source.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SourceController::crawlNow
 * @see app/Http/Controllers/Admin/SourceController.php:194
 * @route '/admin/sources/{source}/crawl'
 */
crawlNow.post = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: crawlNow.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\SourceController::crawlNow
 * @see app/Http/Controllers/Admin/SourceController.php:194
 * @route '/admin/sources/{source}/crawl'
 */
    const crawlNowForm = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: crawlNow.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SourceController::crawlNow
 * @see app/Http/Controllers/Admin/SourceController.php:194
 * @route '/admin/sources/{source}/crawl'
 */
        crawlNowForm.post = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: crawlNow.url(args, options),
            method: 'post',
        })
    
    crawlNow.form = crawlNowForm
/**
* @see \App\Http\Controllers\Admin\SourceController::extractArtifact
 * @see app/Http/Controllers/Admin/SourceController.php:204
 * @route '/admin/sources/artifacts/{artifact}/extract'
 */
export const extractArtifact = (args: { artifact: string | { id: string } } | [artifact: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: extractArtifact.url(args, options),
    method: 'post',
})

extractArtifact.definition = {
    methods: ["post"],
    url: '/admin/sources/artifacts/{artifact}/extract',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SourceController::extractArtifact
 * @see app/Http/Controllers/Admin/SourceController.php:204
 * @route '/admin/sources/artifacts/{artifact}/extract'
 */
extractArtifact.url = (args: { artifact: string | { id: string } } | [artifact: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { artifact: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { artifact: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    artifact: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        artifact: typeof args.artifact === 'object'
                ? args.artifact.id
                : args.artifact,
                }

    return extractArtifact.definition.url
            .replace('{artifact}', parsedArgs.artifact.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SourceController::extractArtifact
 * @see app/Http/Controllers/Admin/SourceController.php:204
 * @route '/admin/sources/artifacts/{artifact}/extract'
 */
extractArtifact.post = (args: { artifact: string | { id: string } } | [artifact: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: extractArtifact.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\SourceController::extractArtifact
 * @see app/Http/Controllers/Admin/SourceController.php:204
 * @route '/admin/sources/artifacts/{artifact}/extract'
 */
    const extractArtifactForm = (args: { artifact: string | { id: string } } | [artifact: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: extractArtifact.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SourceController::extractArtifact
 * @see app/Http/Controllers/Admin/SourceController.php:204
 * @route '/admin/sources/artifacts/{artifact}/extract'
 */
        extractArtifactForm.post = (args: { artifact: string | { id: string } } | [artifact: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: extractArtifact.url(args, options),
            method: 'post',
        })
    
    extractArtifact.form = extractArtifactForm
/**
* @see \App\Http\Controllers\Admin\SourceController::index
 * @see app/Http/Controllers/Admin/SourceController.php:26
 * @route '/admin/sources'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/sources',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SourceController::index
 * @see app/Http/Controllers/Admin/SourceController.php:26
 * @route '/admin/sources'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SourceController::index
 * @see app/Http/Controllers/Admin/SourceController.php:26
 * @route '/admin/sources'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SourceController::index
 * @see app/Http/Controllers/Admin/SourceController.php:26
 * @route '/admin/sources'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SourceController::index
 * @see app/Http/Controllers/Admin/SourceController.php:26
 * @route '/admin/sources'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SourceController::index
 * @see app/Http/Controllers/Admin/SourceController.php:26
 * @route '/admin/sources'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SourceController::index
 * @see app/Http/Controllers/Admin/SourceController.php:26
 * @route '/admin/sources'
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
* @see \App\Http\Controllers\Admin\SourceController::create
 * @see app/Http/Controllers/Admin/SourceController.php:73
 * @route '/admin/sources/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/sources/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SourceController::create
 * @see app/Http/Controllers/Admin/SourceController.php:73
 * @route '/admin/sources/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SourceController::create
 * @see app/Http/Controllers/Admin/SourceController.php:73
 * @route '/admin/sources/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SourceController::create
 * @see app/Http/Controllers/Admin/SourceController.php:73
 * @route '/admin/sources/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SourceController::create
 * @see app/Http/Controllers/Admin/SourceController.php:73
 * @route '/admin/sources/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SourceController::create
 * @see app/Http/Controllers/Admin/SourceController.php:73
 * @route '/admin/sources/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SourceController::create
 * @see app/Http/Controllers/Admin/SourceController.php:73
 * @route '/admin/sources/create'
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
* @see \App\Http\Controllers\Admin\SourceController::store
 * @see app/Http/Controllers/Admin/SourceController.php:99
 * @route '/admin/sources'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/sources',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SourceController::store
 * @see app/Http/Controllers/Admin/SourceController.php:99
 * @route '/admin/sources'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SourceController::store
 * @see app/Http/Controllers/Admin/SourceController.php:99
 * @route '/admin/sources'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\SourceController::store
 * @see app/Http/Controllers/Admin/SourceController.php:99
 * @route '/admin/sources'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SourceController::store
 * @see app/Http/Controllers/Admin/SourceController.php:99
 * @route '/admin/sources'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\SourceController::show
 * @see app/Http/Controllers/Admin/SourceController.php:119
 * @route '/admin/sources/{source}'
 */
export const show = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/sources/{source}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SourceController::show
 * @see app/Http/Controllers/Admin/SourceController.php:119
 * @route '/admin/sources/{source}'
 */
show.url = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { source: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { source: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    source: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        source: typeof args.source === 'object'
                ? args.source.id
                : args.source,
                }

    return show.definition.url
            .replace('{source}', parsedArgs.source.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SourceController::show
 * @see app/Http/Controllers/Admin/SourceController.php:119
 * @route '/admin/sources/{source}'
 */
show.get = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SourceController::show
 * @see app/Http/Controllers/Admin/SourceController.php:119
 * @route '/admin/sources/{source}'
 */
show.head = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SourceController::show
 * @see app/Http/Controllers/Admin/SourceController.php:119
 * @route '/admin/sources/{source}'
 */
    const showForm = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SourceController::show
 * @see app/Http/Controllers/Admin/SourceController.php:119
 * @route '/admin/sources/{source}'
 */
        showForm.get = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SourceController::show
 * @see app/Http/Controllers/Admin/SourceController.php:119
 * @route '/admin/sources/{source}'
 */
        showForm.head = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\SourceController::edit
 * @see app/Http/Controllers/Admin/SourceController.php:138
 * @route '/admin/sources/{source}/edit'
 */
export const edit = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/sources/{source}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SourceController::edit
 * @see app/Http/Controllers/Admin/SourceController.php:138
 * @route '/admin/sources/{source}/edit'
 */
edit.url = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { source: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { source: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    source: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        source: typeof args.source === 'object'
                ? args.source.id
                : args.source,
                }

    return edit.definition.url
            .replace('{source}', parsedArgs.source.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SourceController::edit
 * @see app/Http/Controllers/Admin/SourceController.php:138
 * @route '/admin/sources/{source}/edit'
 */
edit.get = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SourceController::edit
 * @see app/Http/Controllers/Admin/SourceController.php:138
 * @route '/admin/sources/{source}/edit'
 */
edit.head = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SourceController::edit
 * @see app/Http/Controllers/Admin/SourceController.php:138
 * @route '/admin/sources/{source}/edit'
 */
    const editForm = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SourceController::edit
 * @see app/Http/Controllers/Admin/SourceController.php:138
 * @route '/admin/sources/{source}/edit'
 */
        editForm.get = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SourceController::edit
 * @see app/Http/Controllers/Admin/SourceController.php:138
 * @route '/admin/sources/{source}/edit'
 */
        editForm.head = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\SourceController::update
 * @see app/Http/Controllers/Admin/SourceController.php:165
 * @route '/admin/sources/{source}'
 */
export const update = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/sources/{source}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\SourceController::update
 * @see app/Http/Controllers/Admin/SourceController.php:165
 * @route '/admin/sources/{source}'
 */
update.url = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { source: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { source: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    source: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        source: typeof args.source === 'object'
                ? args.source.id
                : args.source,
                }

    return update.definition.url
            .replace('{source}', parsedArgs.source.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SourceController::update
 * @see app/Http/Controllers/Admin/SourceController.php:165
 * @route '/admin/sources/{source}'
 */
update.put = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\SourceController::update
 * @see app/Http/Controllers/Admin/SourceController.php:165
 * @route '/admin/sources/{source}'
 */
update.patch = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\SourceController::update
 * @see app/Http/Controllers/Admin/SourceController.php:165
 * @route '/admin/sources/{source}'
 */
    const updateForm = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SourceController::update
 * @see app/Http/Controllers/Admin/SourceController.php:165
 * @route '/admin/sources/{source}'
 */
        updateForm.put = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\SourceController::update
 * @see app/Http/Controllers/Admin/SourceController.php:165
 * @route '/admin/sources/{source}'
 */
        updateForm.patch = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\SourceController::destroy
 * @see app/Http/Controllers/Admin/SourceController.php:183
 * @route '/admin/sources/{source}'
 */
export const destroy = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/sources/{source}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\SourceController::destroy
 * @see app/Http/Controllers/Admin/SourceController.php:183
 * @route '/admin/sources/{source}'
 */
destroy.url = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { source: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { source: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    source: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        source: typeof args.source === 'object'
                ? args.source.id
                : args.source,
                }

    return destroy.definition.url
            .replace('{source}', parsedArgs.source.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SourceController::destroy
 * @see app/Http/Controllers/Admin/SourceController.php:183
 * @route '/admin/sources/{source}'
 */
destroy.delete = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\SourceController::destroy
 * @see app/Http/Controllers/Admin/SourceController.php:183
 * @route '/admin/sources/{source}'
 */
    const destroyForm = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SourceController::destroy
 * @see app/Http/Controllers/Admin/SourceController.php:183
 * @route '/admin/sources/{source}'
 */
        destroyForm.delete = (args: { source: string | { id: string } } | [source: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const SourceController = { crawlNow, extractArtifact, index, create, store, show, edit, update, destroy }

export default SourceController