/**
 * Category Photos Utility
 * Maps crop categories strictly to the images provided in src/assets by their name:
 * - cofee.jpg
 * - fruits.png
 * - grains.jpg
 * - oilseed.jpg
 * - pulses.jpg
 * - spices.jpg
 * - vegtable.png
 *
 * NOTE: These images are ONLY used for category photos, NOT as default/fallback photos for crop listings.
 */

import coffeeImg from '@/assets/categories/coffee.jpg'
import fruitsImg from '@/assets/categories/fruits.png'
import grainsImg from '@/assets/categories/grains.jpg'
import oilseedImg from '@/assets/categories/oilseeds.jpg'
import pulsesImg from '@/assets/categories/pulses.jpg'
import spicesImg from '@/assets/categories/spices.jpg'
import vegetablesImg from '@/assets/categories/vegetables.png'

export const CATEGORY_PHOTOS = {
    coffee: coffeeImg,
    cofee: coffeeImg,
    grains: grainsImg,
    grain: grainsImg,
    wheat: grainsImg,
    teff: grainsImg,
    cereals: grainsImg,
    spices: spicesImg,
    spice: spicesImg,
    oilseeds: oilseedImg,
    oilseed: oilseedImg,
    sesame: oilseedImg,
    pulses: pulsesImg,
    pulse: pulsesImg,
    beans: pulsesImg,
    bean: pulsesImg,
    lentils: pulsesImg,
    vegetables: vegetablesImg,
    vegetable: vegetablesImg,
    vegtable: vegetablesImg,
    fruits: fruitsImg,
    fruit: fruitsImg,
}

/**
 * Get category photo by category name or slug
 * @param {string} categoryKey 
 * @returns {string|null}
 */
export function getCategoryPhoto(categoryKey) {
    if (!categoryKey) return null
    const key = String(categoryKey).toLowerCase().trim()
    return CATEGORY_PHOTOS[key] || null
}

export {
    coffeeImg,
    fruitsImg,
    grainsImg,
    oilseedImg,
    pulsesImg,
    spicesImg,
    vegetablesImg,
}
