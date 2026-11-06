export function formatETB(amount) {
    if (amount === undefined || amount === null || isNaN(amount)) return '0.00 Br'
    const val = Number(amount) || 0
    return `${val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} Br`
}

export function formatDate(date) {
    if (!date) return 'Recently'
    try {
        const d = typeof date === 'string' || typeof date === 'number' ? new Date(date) : date
        if (!d || !(d instanceof Date) || isNaN(d.getTime())) return 'Recently'
        return d.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        })
    } catch {
        return 'Recently'
    }
}

/**
 * Simple class name merge utility.
 * Joins all truthy arguments into a single class string.
 */
export function cn(...inputs) {
    return inputs.filter(Boolean).join(' ')
}
