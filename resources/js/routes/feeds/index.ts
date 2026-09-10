import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
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
* @see \App\Http\Controllers\FeedController::state
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
export const state = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: state.url(args, options),
    method: 'get',
})

state.definition = {
    methods: ["get","head"],
    url: '/feeds/state/{stateSlug}.xml',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FeedController::state
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
state.url = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return state.definition.url
            .replace('{stateSlug}', parsedArgs.stateSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FeedController::state
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
state.get = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: state.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\FeedController::state
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
state.head = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: state.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\FeedController::state
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
    const stateForm = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: state.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\FeedController::state
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
        stateForm.get = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: state.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\FeedController::state
 * @see app/Http/Controllers/FeedController.php:28
 * @route '/feeds/state/{stateSlug}.xml'
 */
        stateForm.head = (args: { stateSlug: string | number } | [stateSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: state.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    state.form = stateForm
const feeds = {
    latest: Object.assign(latest, latest),
state: Object.assign(state, state),
}

export default feeds