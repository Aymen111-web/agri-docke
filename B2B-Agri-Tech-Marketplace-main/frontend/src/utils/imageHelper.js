/**
 * Image helper utility for AgriGate.
 * Category images are mapped from the dedicated assets provided by their name.
 * IMPORTANT: Crop listings only display uploaded produce photos, NOT category photos!
 */
import {
    CATEGORY_PHOTOS,
    getCategoryPhoto,
    coffeeImg,
    fruitsImg,
    grainsImg,
    oilseedImg,
    pulsesImg,
    spicesImg,
    vegetablesImg,
} from './categoryImages'

export {
    CATEGORY_PHOTOS,
    getCategoryPhoto,
    coffeeImg,
    fruitsImg,
    grainsImg,
    oilseedImg,
    pulsesImg,
    spicesImg,
    vegetablesImg,
}

export const CATEGORY_IMAGES = CATEGORY_PHOTOS

/**
 * Returns the uploaded produce image for a crop listing.
 * NEVER falls back to category photos: photos in assets are strictly for categories only!
 */
export function getCropImage(itemOrName) {
    if (!itemOrName) return null

    // If an object with an uploaded image URL is provided, return uploaded image
    if (typeof itemOrName === 'object') {
        const path = itemOrName.primaryImage ||
            itemOrName.image_url ||
            itemOrName.image_path ||
            (Array.isArray(itemOrName.images) && itemOrName.images.length > 0 ? itemOrName.images[0] : null)
        if (path) {
            if (typeof path === 'string') {
                if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('blob:') || path.startsWith('data:')) {
                    return path
                }
                const cleanPath = path.replace(/^\/?storage\//, '')
                return `http://127.0.0.1:8000/storage/${cleanPath}`
            }
            if (typeof window !== 'undefined' && (path instanceof File || path instanceof Blob)) {
                return URL.createObjectURL(path)
            }
        }
        return null
    } else if (typeof itemOrName === 'string') {
        if (itemOrName.startsWith('http://') || itemOrName.startsWith('https://') || itemOrName.startsWith('/storage/') || itemOrName.startsWith('blob:') || itemOrName.startsWith('data:')) {
            return itemOrName
        }
    }

    // Do NOT fall back to category photos from assets for crops.
    return null
}

export function getAvatarImage(role) {
    const r = (role || '').toLowerCase()
    if (r.includes('farmer')) return '/images/farmer_avatar.svg'
    if (r.includes('buyer')) return '/images/buyer_avatar.svg'
    return '/images/buyer_avatar.svg'
}

export const EMPTY_STATE_IMAGE = '/images/empty_box.svg'
