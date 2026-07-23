import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-pill text-sm font-bold transition disabled:pointer-events-none disabled:opacity-60 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2 aria-invalid:ring-destructive/20 aria-invalid:border-destructive",
  {
    variants: {
      variant: {
        default:
          "bg-green-500 text-white hover:bg-green-600",
        destructive:
          "bg-destructive text-white hover:bg-destructive/90 focus-visible:ring-destructive/40",
        outline:
          "border border-ink-300 bg-white text-navy-700 hover:border-navy-500 hover:bg-sand-50",
        secondary:
          "bg-navy-50 text-navy-700 hover:bg-navy-100",
        ghost:
          "text-ink-700 hover:bg-sand-100 hover:text-navy-700",
        link: "text-green-600 underline-offset-4 hover:underline",
      },
      size: {
        "default": "h-10 px-5 py-2 has-[>svg]:px-4",
        "sm": "h-9 gap-1.5 px-4 has-[>svg]:px-3",
        "lg": "h-12 px-7 has-[>svg]:px-5",
        "icon": "size-10",
        "icon-sm": "size-9",
        "icon-lg": "size-11",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)
export type ButtonVariants = VariantProps<typeof buttonVariants>
