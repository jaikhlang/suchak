import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\SourceController::extract
 * @see app/Http/Controllers/Admin/SourceController.php:204
 * @route '/admin/sources/artifacts/{artifact}/extract'
 */
export const extract = (args: { artifact: string | { id: string } } | [artifact: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: extract.url(args, options),
    method: 'post',
})

extract.definition = {
    methods: ["post"],
    url: '/admin/sources/artifacts/{artifact}/extract',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SourceController::extract
 * @see app/Http/Controllers/Admin/SourceController.php:204
 * @route '/admin/sources/artifacts/{artifact}/extract'
 */
extract.url = (args: { artifact: string | { id: string } } | [artifact: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return extract.definition.url
            .replace('{artifact}', parsedArgs.artifact.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SourceController::extract
 * @see app/Http/Controllers/Admin/SourceController.php:204
 * @route '/admin/sources/artifacts/{artifact}/extract'
 */
extract.post = (args: { artifact: string | { id: string } } | [artifact: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: extract.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\SourceController::extract
 * @see app/Http/Controllers/Admin/SourceController.php:204
 * @route '/admin/sources/artifacts/{artifact}/extract'
 */
    const extractForm = (args: { artifact: string | { id: string } } | [artifact: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: extract.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SourceController::extract
 * @see app/Http/Controllers/Admin/SourceController.php:204
 * @route '/admin/sources/artifacts/{artifact}/extract'
 */
        extractForm.post = (args: { artifact: string | { id: string } } | [artifact: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: extract.url(args, options),
            method: 'post',
        })
    
    extract.form = extractForm
const artifacts = {
    extract: Object.assign(extract, extract),
}

export default artifacts