import { ref, onMounted, onUnmounted } from 'vue'

/**
 * useScrollAnimation - Composable for scroll-triggered animations
 * 
 * @param {Object} options - Configuration options
 * @param {number} options.threshold - Intersection threshold (0.0 - 1.0)
 * @param {string} options.rootMargin - Root margin for intersection observer
 * @param {boolean} options.once - Trigger animation only once
 * @returns {Object} - { target, isVisible }
 * 
 * @example
 * const { target, isVisible } = useScrollAnimation({ threshold: 0.2, once: true })
 * 
 * <div ref="target" :class="{ 'animate-fade-in': isVisible }">
 *   Content
 * </div>
 */
export function useScrollAnimation(options = {}) {
  const {
    threshold = 0.1,
    rootMargin = '0px',
    once = true
  } = options

  const target = ref(null)
  const isVisible = ref(false)
  let observer = null

  onMounted(() => {
    if (!target.value) return

    observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          isVisible.value = true
          if (once && observer) {
            observer.disconnect()
          }
        } else if (!once) {
          isVisible.value = false
        }
      },
      { threshold, rootMargin }
    )

    observer.observe(target.value)
  })

  onUnmounted(() => {
    if (observer) {
      observer.disconnect()
    }
  })

  return { target, isVisible }
}

/**
 * useStaggerAnimation - Composable for staggered list animations
 * 
 * @param {number} itemCount - Number of items to animate
 * @param {Object} options - Configuration options
 * @param {number} options.baseDelay - Initial delay before first item (ms)
 * @param {number} options.staggerDelay - Delay between each item (ms)
 * @param {boolean} options.once - Trigger animation only once
 * @returns {Object} - { target, isVisible }
 * 
 * @example
 * const items = [1, 2, 3, 4]
 * const { target, isVisible } = useStaggerAnimation(items.length, { staggerDelay: 100 })
 * 
 * <div ref="target">
 *   <div v-for="(item, i) in items" :class="{ 'opacity-100': isVisible(i) }">
 *     {{ item }}
 *   </div>
 * </div>
 */
export function useStaggerAnimation(itemCount, options = {}) {
  const {
    baseDelay = 0,
    staggerDelay = 100,
    once = true
  } = options

  const visibleItems = ref(new Set())
  const target = ref(null)
  let observer = null

  onMounted(() => {
    if (!target.value) return

    observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          // Stagger the appearance
          for (let i = 0; i < itemCount; i++) {
            setTimeout(() => {
              visibleItems.value.add(i)
            }, baseDelay + (i * staggerDelay))
          }
          
          if (once && observer) {
            observer.disconnect()
          }
        } else if (!once) {
          visibleItems.value.clear()
        }
      },
      { threshold: 0.1 }
    )

    observer.observe(target.value)
  })

  onUnmounted(() => {
    if (observer) {
      observer.disconnect()
    }
  })

  const isVisible = (index) => visibleItems.value.has(index)

  return { target, isVisible }
}

/**
 * useParallaxScroll - Composable for parallax scroll effects
 * 
 * @param {Object} options - Configuration options
 * @param {number} options.speed - Parallax speed multiplier (0.0 - 1.0)
 * @returns {Object} - { target, transform }
 * 
 * @example
 * const { target, transform } = useParallaxScroll({ speed: 0.5 })
 * 
 * <div ref="target" :style="{ transform }">
 *   Parallax content
 * </div>
 */
export function useParallaxScroll(options = {}) {
  const { speed = 0.5 } = options
  
  const target = ref(null)
  const transform = ref('translateY(0px)')
  
  const handleScroll = () => {
    if (!target.value) return
    
    const rect = target.value.getBoundingClientRect()
    const scrolled = window.scrollY
    const offset = scrolled * speed
    
    transform.value = `translateY(${offset}px)`
  }
  
  onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true })
    handleScroll()
  })
  
  onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
  })
  
  return { target, transform }
}

/**
 * useCounterAnimation - Composable for animated number counters
 * 
 * @param {number} target - Target number to count to
 * @param {Object} options - Configuration options
 * @param {number} options.duration - Animation duration (ms)
 * @param {Function} options.easing - Easing function
 * @returns {Object} - { current, start }
 * 
 * @example
 * const { current, start } = useCounterAnimation(1000, { duration: 2000 })
 * 
 * <div>{{ Math.floor(current) }}</div>
 * <button @click="start">Start</button>
 */
export function useCounterAnimation(target, options = {}) {
  const {
    duration = 2000,
    easing = (t) => t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t // ease-in-out
  } = options
  
  const current = ref(0)
  let animationFrame = null
  let startTime = null
  
  const animate = (timestamp) => {
    if (!startTime) startTime = timestamp
    
    const elapsed = timestamp - startTime
    const progress = Math.min(elapsed / duration, 1)
    const eased = easing(progress)
    
    current.value = eased * target
    
    if (progress < 1) {
      animationFrame = requestAnimationFrame(animate)
    } else {
      current.value = target
    }
  }
  
  const start = () => {
    startTime = null
    current.value = 0
    animationFrame = requestAnimationFrame(animate)
  }
  
  onUnmounted(() => {
    if (animationFrame) {
      cancelAnimationFrame(animationFrame)
    }
  })
  
  return { current, start }
}

/**
 * useReducedMotion - Check if user prefers reduced motion
 * 
 * @returns {Object} - { prefersReducedMotion }
 * 
 * @example
 * const { prefersReducedMotion } = useReducedMotion()
 * 
 * <div :class="{ 'animate-slide': !prefersReducedMotion }">
 *   Content
 * </div>
 */
export function useReducedMotion() {
  const prefersReducedMotion = ref(false)
  
  onMounted(() => {
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)')
    prefersReducedMotion.value = mediaQuery.matches
    
    const handleChange = (e) => {
      prefersReducedMotion.value = e.matches
    }
    
    mediaQuery.addEventListener('change', handleChange)
    
    onUnmounted(() => {
      mediaQuery.removeEventListener('change', handleChange)
    })
  })
  
  return { prefersReducedMotion }
}
