import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::index
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:23
 * @route '/admin/notices'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/notices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::index
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:23
 * @route '/admin/notices'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::index
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:23
 * @route '/admin/notices'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::index
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:23
 * @route '/admin/notices'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\NoticeManagementController::index
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:23
 * @route '/admin/notices'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\NoticeManagementController::index
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:23
 * @route '/admin/notices'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\NoticeManagementController::index
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:23
 * @route '/admin/notices'
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
* @see \App\Http\Controllers\Admin\NoticeManagementController::show
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:89
 * @route '/admin/notices/{notice}'
 */
export const show = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/notices/{notice}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::show
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:89
 * @route '/admin/notices/{notice}'
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
* @see \App\Http\Controllers\Admin\NoticeManagementController::show
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:89
 * @route '/admin/notices/{notice}'
 */
show.get = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::show
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:89
 * @route '/admin/notices/{notice}'
 */
show.head = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\NoticeManagementController::show
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:89
 * @route '/admin/notices/{notice}'
 */
    const showForm = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\NoticeManagementController::show
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:89
 * @route '/admin/notices/{notice}'
 */
        showForm.get = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\NoticeManagementController::show
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:89
 * @route '/admin/notices/{notice}'
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
* @see \App\Http\Controllers\Admin\NoticeManagementController::republish
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:115
 * @route '/admin/notices/{notice}/republish'
 */
export const republish = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: republish.url(args, options),
    method: 'post',
})

republish.definition = {
    methods: ["post"],
    url: '/admin/notices/{notice}/republish',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::republish
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:115
 * @route '/admin/notices/{notice}/republish'
 */
republish.url = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return republish.definition.url
            .replace('{notice}', parsedArgs.notice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::republish
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:115
 * @route '/admin/notices/{notice}/republish'
 */
republish.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: republish.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\NoticeManagementController::republish
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:115
 * @route '/admin/notices/{notice}/republish'
 */
    const republishForm = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: republish.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\NoticeManagementController::republish
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:115
 * @route '/admin/notices/{notice}/republish'
 */
        republishForm.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: republish.url(args, options),
            method: 'post',
        })
    
    republish.form = republishForm
/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::archive
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:125
 * @route '/admin/notices/{notice}/archive'
 */
export const archive = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: archive.url(args, options),
    method: 'post',
})

archive.definition = {
    methods: ["post"],
    url: '/admin/notices/{notice}/archive',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::archive
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:125
 * @route '/admin/notices/{notice}/archive'
 */
archive.url = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return archive.definition.url
            .replace('{notice}', parsedArgs.notice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::archive
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:125
 * @route '/admin/notices/{notice}/archive'
 */
archive.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: archive.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\NoticeManagementController::archive
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:125
 * @route '/admin/notices/{notice}/archive'
 */
    const archiveForm = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: archive.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\NoticeManagementController::archive
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:125
 * @route '/admin/notices/{notice}/archive'
 */
        archiveForm.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: archive.url(args, options),
            method: 'post',
        })
    
    archive.form = archiveForm
/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::dispatchDistribution
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:135
 * @route '/admin/notices/{notice}/dispatch-distribution'
 */
export const dispatchDistribution = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: dispatchDistribution.url(args, options),
    method: 'post',
})

dispatchDistribution.definition = {
    methods: ["post"],
    url: '/admin/notices/{notice}/dispatch-distribution',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::dispatchDistribution
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:135
 * @route '/admin/notices/{notice}/dispatch-distribution'
 */
dispatchDistribution.url = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return dispatchDistribution.definition.url
            .replace('{notice}', parsedArgs.notice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\NoticeManagementController::dispatchDistribution
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:135
 * @route '/admin/notices/{notice}/dispatch-distribution'
 */
dispatchDistribution.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: dispatchDistribution.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\NoticeManagementController::dispatchDistribution
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:135
 * @route '/admin/notices/{notice}/dispatch-distribution'
 */
    const dispatchDistributionForm = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: dispatchDistribution.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\NoticeManagementController::dispatchDistribution
 * @see app/Http/Controllers/Admin/NoticeManagementController.php:135
 * @route '/admin/notices/{notice}/dispatch-distribution'
 */
        dispatchDistributionForm.post = (args: { notice: string | { id: string } } | [notice: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: dispatchDistribution.url(args, options),
            method: 'post',
        })
    
    dispatchDistribution.form = dispatchDistributionForm
const NoticeManagementController = { index, show, republish, archive, dispatchDistribution }

export default NoticeManagementController