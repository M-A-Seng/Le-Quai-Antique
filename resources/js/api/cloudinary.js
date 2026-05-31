const CLOUDINARY_CLOUD_NAME = document.querySelector('meta[name="cloudinary-cloud-name"]')?.content;
const CLOUDINARY_BASE = `https://res.cloudinary.com/${CLOUDINARY_CLOUD_NAME}/image/upload`;

export function cloudinaryUrl(publicId, options = {}) {
    const {
        width = null, // si fourni override viewport
        useViewport = false, // sinon calcule viewport
        maxWidth = 2000,
        quality = "auto",
        format = "auto",
        cover = false,
        ratio = window.devicePixelRatio || 1, // combien pixels physiques écran client
        vwRatio = 0.9 // % du viewport
    } = options;

    if (width) {
        targetWidth = width;
    }
    else if (useViewport) {
        const vw = window.innerWidth || document.documentElement.clientWidth;
        targetWidth = Math.ceil(vw * vwRatio * ratio); // estimation taille optimale
        targetWidth = Math.min(targetWidth, maxWidth); // le plus petit width pour éviter images trop lourdes
    }
    else {
        targetWidth = 800; // fallback
    }
    // Cloudinary transformations
    const transformations = [
        `f_${format}`,
        `q_${quality}`,
        `w_${targetWidth}`,
        cover ? "c_fill" : ""
    ].filter(Boolean).join(",");

    return `${CLOUDINARY_BASE}/${transformations}/${publicId}`;
}
