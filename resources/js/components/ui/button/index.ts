import { cva, type VariantProps } from 'class-variance-authority'

export { default as Button } from './Button.vue'

export const buttonVariants = cva(
  'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg text-sm font-medium transition-smooth disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*=\'size-\'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive relative overflow-hidden',
  {
    variants: {
      variant: {
        default:
          'bg-primary text-primary-foreground shadow-soft hover:bg-primary/90 hover:shadow-medium hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0',
        destructive:
          'bg-destructive text-destructive-foreground shadow-soft hover:bg-destructive/90 hover:shadow-medium hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40',
        outline:
          'border border-border bg-background shadow-soft hover:bg-secondary hover:text-secondary-foreground hover:shadow-medium hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0 dark:bg-input/30 dark:border-input dark:hover:bg-input/50',
        secondary:
          'bg-secondary text-secondary-foreground shadow-soft hover:bg-secondary/80 hover:shadow-medium hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0',
        ghost:
          'hover:bg-secondary hover:text-secondary-foreground hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0',
        link: 'text-primary underline-offset-4 hover:underline hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0',
        success:
          'bg-success text-success-foreground shadow-soft hover:bg-success/90 hover:shadow-medium hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0',
        warning:
          'bg-warning text-warning-foreground shadow-soft hover:bg-warning/90 hover:shadow-medium hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0',
        glass:
          'glass text-foreground shadow-soft hover:shadow-medium hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0',
        gradient:
          'bg-gradient-to-r from-primary to-accent text-primary-foreground shadow-soft hover:shadow-medium hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0',
        premium:
          'bg-gradient-to-r from-primary via-accent to-primary bg-size-200 animate-gradient text-primary-foreground shadow-glow hover:shadow-strong hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0',
      },
      size: {
        xs: 'h-7 rounded-md gap-1 px-2.5 has-[>svg]:px-2 text-xs',
        sm: 'h-8 rounded-lg gap-1.5 px-3 has-[>svg]:px-2.5 text-xs',
        default: 'h-10 px-4 py-2 has-[>svg]:px-3',
        lg: 'h-12 rounded-xl px-6 has-[>svg]:px-4 text-base',
        xl: 'h-14 rounded-xl px-8 has-[>svg]:px-6 text-lg',
        '2xl': 'h-16 rounded-2xl px-10 has-[>svg]:px-8 text-xl',
        icon: 'size-10',
        'icon-xs': 'size-7 rounded-md',
        'icon-sm': 'size-8',
        'icon-lg': 'size-12',
        'icon-xl': 'size-14',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  },
)

export type ButtonVariants = VariantProps<typeof buttonVariants>
