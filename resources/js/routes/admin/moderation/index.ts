import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\ModerationController::index
 * @see app/Http/Controllers/Admin/ModerationController.php:21
 * @route '/admin/moderation'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/moderation',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ModerationController::index
 * @see app/Http/Controllers/Admin/ModerationController.php:21
 * @route '/admin/moderation'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ModerationController::index
 * @see app/Http/Controllers/Admin/ModerationController.php:21
 * @route '/admin/moderation'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\ModerationController::index
 * @see app/Http/Controllers/Admin/ModerationController.php:21
 * @route '/admin/moderation'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\ModerationController::index
 * @see app/Http/Controllers/Admin/ModerationController.php:21
 * @route '/admin/moderation'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\ModerationController::index
 * @see app/Http/Controllers/Admin/ModerationController.php:21
 * @route '/admin/moderation'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\ModerationController::index
 * @see app/Http/Controllers/Admin/ModerationController.php:21
 * @route '/admin/moderation'
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
* @see \App\Http\Controllers\Admin\ModerationController::show
 * @see app/Http/Controllers/Admin/ModerationController.php:81
 * @route '/admin/moderation/{notice}'
 */
export const show = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/moderation/{notice}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ModerationController::show
 * @see app/Http/Controllers/Admin/ModerationController.php:81
 * @route '/admin/moderation/{notice}'
 */
show.url = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { notice: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { notice: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    notice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        notice: typeof args.notice === 'object'
                ? args.notice.id
                : args.notice,
                }

    return show.definition.url
            .replace('{notice}', parsedArgs.notice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ModerationController::show
 * @see app/Http/Controllers/Admin/ModerationController.php:81
 * @route '/admin/moderation/{notice}'
 */
show.get = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\ModerationController::show
 * @see app/Http/Controllers/Admin/ModerationController.php:81
 * @route '/admin/moderation/{notice}'
 */
show.head = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\ModerationController::show
 * @see app/Http/Controllers/Admin/ModerationController.php:81
 * @route '/admin/moderation/{notice}'
 */
    const showForm = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\ModerationController::show
 * @see app/Http/Controllers/Admin/ModerationController.php:81
 * @route '/admin/moderation/{notice}'
 */
        showForm.get = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\ModerationController::show
 * @see app/Http/Controllers/Admin/ModerationController.php:81
 * @route '/admin/moderation/{notice}'
 */
        showForm.head = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\ModerationController::approve
 * @see app/Http/Controllers/Admin/ModerationController.php:119
 * @route '/admin/moderation/{notice}/approve'
 */
export const approve = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

approve.definition = {
    methods: ["post"],
    url: '/admin/moderation/{notice}/approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ModerationController::approve
 * @see app/Http/Controllers/Admin/ModerationController.php:119
 * @route '/admin/moderation/{notice}/approve'
 */
approve.url = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { notice: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { notice: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    notice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        notice: typeof args.notice === 'object'
                ? args.notice.id
                : args.notice,
                }

    return approve.definition.url
            .replace('{notice}', parsedArgs.notice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ModerationController::approve
 * @see app/Http/Controllers/Admin/ModerationController.php:119
 * @route '/admin/moderation/{notice}/approve'
 */
approve.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ModerationController::approve
 * @see app/Http/Controllers/Admin/ModerationController.php:119
 * @route '/admin/moderation/{notice}/approve'
 */
    const approveForm = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: approve.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ModerationController::approve
 * @see app/Http/Controllers/Admin/ModerationController.php:119
 * @route '/admin/moderation/{notice}/approve'
 */
        approveForm.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: approve.url(args, options),
            method: 'post',
        })
    
    approve.form = approveForm
/**
* @see \App\Http\Controllers\Admin\ModerationController::reject
 * @see app/Http/Controllers/Admin/ModerationController.php:146
 * @route '/admin/moderation/{notice}/reject'
 */
export const reject = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

reject.definition = {
    methods: ["post"],
    url: '/admin/moderation/{notice}/reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ModerationController::reject
 * @see app/Http/Controllers/Admin/ModerationController.php:146
 * @route '/admin/moderation/{notice}/reject'
 */
reject.url = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { notice: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { notice: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    notice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        notice: typeof args.notice === 'object'
                ? args.notice.id
                : args.notice,
                }

    return reject.definition.url
            .replace('{notice}', parsedArgs.notice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ModerationController::reject
 * @see app/Http/Controllers/Admin/ModerationController.php:146
 * @route '/admin/moderation/{notice}/reject'
 */
reject.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ModerationController::reject
 * @see app/Http/Controllers/Admin/ModerationController.php:146
 * @route '/admin/moderation/{notice}/reject'
 */
    const rejectForm = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: reject.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ModerationController::reject
 * @see app/Http/Controllers/Admin/ModerationController.php:146
 * @route '/admin/moderation/{notice}/reject'
 */
        rejectForm.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: reject.url(args, options),
            method: 'post',
        })
    
    reject.form = rejectForm
/**
* @see \App\Http\Controllers\Admin\ModerationController::corrigendum
 * @see app/Http/Controllers/Admin/ModerationController.php:193
 * @route '/admin/moderation/{notice}/corrigendum'
 */
export const corrigendum = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: corrigendum.url(args, options),
    method: 'post',
})

corrigendum.definition = {
    methods: ["post"],
    url: '/admin/moderation/{notice}/corrigendum',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ModerationController::corrigendum
 * @see app/Http/Controllers/Admin/ModerationController.php:193
 * @route '/admin/moderation/{notice}/corrigendum'
 */
corrigendum.url = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { notice: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { notice: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    notice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        notice: typeof args.notice === 'object'
                ? args.notice.id
                : args.notice,
                }

    return corrigendum.definition.url
            .replace('{notice}', parsedArgs.notice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ModerationController::corrigendum
 * @see app/Http/Controllers/Admin/ModerationController.php:193
 * @route '/admin/moderation/{notice}/corrigendum'
 */
corrigendum.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: corrigendum.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ModerationController::corrigendum
 * @see app/Http/Controllers/Admin/ModerationController.php:193
 * @route '/admin/moderation/{notice}/corrigendum'
 */
    const corrigendumForm = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: corrigendum.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ModerationController::corrigendum
 * @see app/Http/Controllers/Admin/ModerationController.php:193
 * @route '/admin/moderation/{notice}/corrigendum'
 */
        corrigendumForm.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: corrigendum.url(args, options),
            method: 'post',
        })
    
    corrigendum.form = corrigendumForm
/**
* @see \App\Http\Controllers\Admin\ModerationController::mergeDuplicate
 * @see app/Http/Controllers/Admin/ModerationController.php:236
 * @route '/admin/moderation/{notice}/merge-duplicate'
 */
export const mergeDuplicate = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: mergeDuplicate.url(args, options),
    method: 'post',
})

mergeDuplicate.definition = {
    methods: ["post"],
    url: '/admin/moderation/{notice}/merge-duplicate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ModerationController::mergeDuplicate
 * @see app/Http/Controllers/Admin/ModerationController.php:236
 * @route '/admin/moderation/{notice}/merge-duplicate'
 */
mergeDuplicate.url = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { notice: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { notice: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    notice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        notice: typeof args.notice === 'object'
                ? args.notice.id
                : args.notice,
                }

    return mergeDuplicate.definition.url
            .replace('{notice}', parsedArgs.notice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ModerationController::mergeDuplicate
 * @see app/Http/Controllers/Admin/ModerationController.php:236
 * @route '/admin/moderation/{notice}/merge-duplicate'
 */
mergeDuplicate.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: mergeDuplicate.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ModerationController::mergeDuplicate
 * @see app/Http/Controllers/Admin/ModerationController.php:236
 * @route '/admin/moderation/{notice}/merge-duplicate'
 */
    const mergeDuplicateForm = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: mergeDuplicate.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ModerationController::mergeDuplicate
 * @see app/Http/Controllers/Admin/ModerationController.php:236
 * @route '/admin/moderation/{notice}/merge-duplicate'
 */
        mergeDuplicateForm.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: mergeDuplicate.url(args, options),
            method: 'post',
        })
    
    mergeDuplicate.form = mergeDuplicateForm
/**
* @see \App\Http\Controllers\Admin\ModerationController::update
 * @see app/Http/Controllers/Admin/ModerationController.php:173
 * @route '/admin/moderation/{notice}'
 */
export const update = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/admin/moderation/{notice}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Admin\ModerationController::update
 * @see app/Http/Controllers/Admin/ModerationController.php:173
 * @route '/admin/moderation/{notice}'
 */
update.url = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { notice: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { notice: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    notice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        notice: typeof args.notice === 'object'
                ? args.notice.id
                : args.notice,
                }

    return update.definition.url
            .replace('{notice}', parsedArgs.notice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ModerationController::update
 * @see app/Http/Controllers/Admin/ModerationController.php:173
 * @route '/admin/moderation/{notice}'
 */
update.put = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Admin\ModerationController::update
 * @see app/Http/Controllers/Admin/ModerationController.php:173
 * @route '/admin/moderation/{notice}'
 */
    const updateForm = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ModerationController::update
 * @see app/Http/Controllers/Admin/ModerationController.php:173
 * @route '/admin/moderation/{notice}'
 */
        updateForm.put = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const moderation = {
    index: Object.assign(index, index),
show: Object.assign(show, show),
approve: Object.assign(approve, approve),
reject: Object.assign(reject, reject),
corrigendum: Object.assign(corrigendum, corrigendum),
mergeDuplicate: Object.assign(mergeDuplicate, mergeDuplicate),
update: Object.assign(update, update),
}

export default moderation