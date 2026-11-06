/**
 * Automatically resizes and compresses an image file using HTML5 Canvas.
 * Returns a Promise resolving to a compressed File object (or original file if not an image / fails).
 */
export async function compressImageFile(file, maxWidth = 1400, maxHeight = 1400, quality = 0.8) {
  if (!file || !file.type || !file.type.startsWith('image/')) {
    return file
  }

  // If file is already smaller than 300KB, no need to compress
  if (file.size <= 300 * 1024) {
    return file
  }

  return new Promise((resolve) => {
    const reader = new FileReader()
    reader.readAsDataURL(file)
    reader.onload = (event) => {
      const img = new Image()
      img.src = event.target.result
      img.onload = () => {
        let width = img.width
        let height = img.height

        if (width > maxWidth || height > maxHeight) {
          if (width > height) {
            height = Math.round((height * maxWidth) / width)
            width = maxWidth
          } else {
            width = Math.round((width * maxHeight) / height)
            height = maxHeight
          }
        }

        const canvas = document.createElement('canvas')
        canvas.width = width
        canvas.height = height

        const ctx = canvas.getContext('2d')
        ctx.drawImage(img, 0, 0, width, height)

        canvas.toBlob(
          (blob) => {
            if (!blob) {
              resolve(file)
              return
            }
            const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
              type: 'image/jpeg',
              lastModified: Date.now(),
            })
            resolve(compressedFile)
          },
          'image/jpeg',
          quality
        )
      }
      img.onerror = () => resolve(file)
    }
    reader.onerror = () => resolve(file)
  })
}

/**
 * Compress multiple files sequentially or in parallel
 */
export async function compressImageFiles(files, maxWidth = 1400, maxHeight = 1400, quality = 0.8) {
  if (!files || !files.length) return []
  return Promise.all(Array.from(files).map(f => compressImageFile(f, maxWidth, maxHeight, quality)))
}
