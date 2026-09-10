import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\SubscriptionController::store
 * @see app/Http/Controllers/SubscriptionController.php:12
 * @route '/subscriptions'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/subscriptions',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SubscriptionController::store
 * @see app/Http/Controllers/SubscriptionController.php:12
 * @route '/subscriptions'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SubscriptionController::store
 * @see app/Http/Controllers/SubscriptionController.php:12
 * @route '/subscriptions'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\SubscriptionController::store
 * @see app/Http/Controllers/SubscriptionController.php:12
 * @route '/subscriptions'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\SubscriptionController::store
 * @see app/Http/Controllers/SubscriptionController.php:12
 * @route '/subscriptions'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\SubscriptionController::destroy
 * @see app/Http/Controllers/SubscriptionController.php:39
 * @route '/subscriptions/{token}'
 */
export const destroy = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: destroy.url(args, options),
    method: 'get',
})

destroy.definition = {
    methods: ["get","head"],
    url: '/subscriptions/{token}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SubscriptionController::destroy
 * @see app/Http/Controllers/SubscriptionController.php:39
 * @route '/subscriptions/{token}'
 */
destroy.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { token: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    token: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        token: args.token,
                }

    return destroy.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SubscriptionController::destroy
 * @see app/Http/Controllers/SubscriptionController.php:39
 * @route '/subscriptions/{token}'
 */
destroy.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: destroy.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SubscriptionController::destroy
 * @see app/Http/Controllers/SubscriptionController.php:39
 * @route '/subscriptions/{token}'
 */
destroy.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: destroy.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SubscriptionController::destroy
 * @see app/Http/Controllers/SubscriptionController.php:39
 * @route '/subscriptions/{token}'
 */
    const destroyForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: destroy.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SubscriptionController::destroy
 * @see app/Http/Controllers/SubscriptionController.php:39
 * @route '/subscriptions/{token}'
 */
        destroyForm.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: destroy.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SubscriptionController::destroy
 * @see app/Http/Controllers/SubscriptionController.php:39
 * @route '/subscriptions/{token}'
 */
        destroyForm.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    destroy.form = destroyForm
const SubscriptionController = { store, destroy }

export default SubscriptionController