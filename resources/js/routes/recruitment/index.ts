import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\RecruitmentController::index
 * @see app/Http/Controllers/RecruitmentController.php:57
 * @route '/recruitment'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/recruitment',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RecruitmentController::index
 * @see app/Http/Controllers/RecruitmentController.php:57
 * @route '/recruitment'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RecruitmentController::index
 * @see app/Http/Controllers/RecruitmentController.php:57
 * @route '/recruitment'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\RecruitmentController::index
 * @see app/Http/Controllers/RecruitmentController.php:57
 * @route '/recruitment'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\RecruitmentController::index
 * @see app/Http/Controllers/RecruitmentController.php:57
 * @route '/recruitment'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\RecruitmentController::index
 * @see app/Http/Controllers/RecruitmentController.php:57
 * @route '/recruitment'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\RecruitmentController::index
 * @see app/Http/Controllers/RecruitmentController.php:57
 * @route '/recruitment'
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
* @see \App\Http\Controllers\RecruitmentController::show
 * @see app/Http/Controllers/RecruitmentController.php:146
 * @route '/recruitment/{institutionSlug}/{postSlug}'
 */
export const show = (args: { institutionSlug: string | number, postSlug: string | number } | [institutionSlug: string | number, postSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/recruitment/{institutionSlug}/{postSlug}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RecruitmentController::show
 * @see app/Http/Controllers/RecruitmentController.php:146
 * @route '/recruitment/{institutionSlug}/{postSlug}'
 */
show.url = (args: { institutionSlug: string | number, postSlug: string | number } | [institutionSlug: string | number, postSlug: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    institutionSlug: args[0],
                    postSlug: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        institutionSlug: args.institutionSlug,
                                postSlug: args.postSlug,
                }

    return show.definition.url
            .replace('{institutionSlug}', parsedArgs.institutionSlug.toString())
            .replace('{postSlug}', parsedArgs.postSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RecruitmentController::show
 * @see app/Http/Controllers/RecruitmentController.php:146
 * @route '/recruitment/{institutionSlug}/{postSlug}'
 */
show.get = (args: { institutionSlug: string | number, postSlug: string | number } | [institutionSlug: string | number, postSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\RecruitmentController::show
 * @see app/Http/Controllers/RecruitmentController.php:146
 * @route '/recruitment/{institutionSlug}/{postSlug}'
 */
show.head = (args: { institutionSlug: string | number, postSlug: string | number } | [institutionSlug: string | number, postSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\RecruitmentController::show
 * @see app/Http/Controllers/RecruitmentController.php:146
 * @route '/recruitment/{institutionSlug}/{postSlug}'
 */
    const showForm = (args: { institutionSlug: string | number, postSlug: string | number } | [institutionSlug: string | number, postSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\RecruitmentController::show
 * @see app/Http/Controllers/RecruitmentController.php:146
 * @route '/recruitment/{institutionSlug}/{postSlug}'
 */
        showForm.get = (args: { institutionSlug: string | number, postSlug: string | number } | [institutionSlug: string | number, postSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\RecruitmentController::show
 * @see app/Http/Controllers/RecruitmentController.php:146
 * @route '/recruitment/{institutionSlug}/{postSlug}'
 */
        showForm.head = (args: { institutionSlug: string | number, postSlug: string | number } | [institutionSlug: string | number, postSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const recruitment = {
    index: Object.assign(index, index),
show: Object.assign(show, show),
}

export default recruitment