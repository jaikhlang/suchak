import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\FeedController::latest
 * @see app/Http/Controllers/FeedController.php:12
 * @route '/feeds/latest.xml'
 */
export const latest = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: latest.url(options),
    method: 'get',
})

latest.definition = {
    methods: ["get","head"],
    url: '/feeds/latest.xml',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FeedController::latest
 * @see app/Http/Controllers/FeedController.php:12
 * @route '/feeds/latest.xml'
 */
latest.url = (options?: RouteQueryOptions) => {
    return latest.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FeedController::latest
 * @see app/Http/Controllers/FeedController.php:12
 * @route '/feeds/latest.xml'
 */
latest.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: latest.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\FeedController::latest
 * @see app/Http/Controllers/FeedController.php:12
 * @route '/feeds/latest.xml'
 */
latest.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: latest.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\FeedController::latest
 * @see app/Http/Controllers/FeedController.php:12
 * @route '/feeds/latest.xml'
 */
    const latestForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: latest.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\FeedController::latest
 * @see app/Http/Controllers/FeedController.php:12
 * @route '/feeds/latest.xml'
 */
        latestForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: latest.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\FeedController::latest
 * @see app/Http/Controllers/FeedController.php:12
 * @route '/feeds/latest.xml'
 */
        latestForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: latest.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    latest.form = latestForm
/**
* @see \App\Http\Controllers\FeedController::byState
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
export const byState = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: byState.url(args, options),
    method: 'get',
})

byState.definition = {
    methods: ["get","head"],
    url: '/feeds/state/{stateSlug}.xml',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FeedController::byState
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
byState.url = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { stateSlug: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    stateSlug: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        stateSlug: args.stateSlug,
                }

    return byState.definition.url
            .replace('{stateSlug}', parsedArgs.stateSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FeedController::byState
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
byState.get = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: byState.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\FeedController::byState
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
byState.head = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: byState.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\FeedController::byState
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
    const byStateForm = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: byState.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\FeedController::byState
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
        byStateForm.get = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: byState.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\FeedController::byState
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
        byStateForm.head = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: byState.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    byState.form = byStateForm
const FeedController = { latest, byState }

export default FeedController