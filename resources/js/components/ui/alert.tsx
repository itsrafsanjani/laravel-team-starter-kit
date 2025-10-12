import { cva, type VariantProps } from 'class-variance-authority';
import { AlertCircle, CheckCircle, Info, X } from 'lucide-react';
import { type HTMLAttributes, forwardRef } from 'react';
import { cn } from '@/lib/utils';

const alertVariants = cva(
  'relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground',
  {
    variants: {
      variant: {
        default: 'bg-background text-foreground',
        destructive:
          'border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive',
        success:
          'border-green-500/50 text-green-700 bg-green-50 dark:border-green-500 dark:text-green-400 dark:bg-green-950 [&>svg]:text-green-600 dark:[&>svg]:text-green-400',
        warning:
          'border-yellow-500/50 text-yellow-700 bg-yellow-50 dark:border-yellow-500 dark:text-yellow-400 dark:bg-yellow-950 [&>svg]:text-yellow-600 dark:[&>svg]:text-yellow-400',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  }
);

const Alert = forwardRef<
  HTMLDivElement,
  HTMLAttributes<HTMLDivElement> & VariantProps<typeof alertVariants>
>(({ className, variant, ...props }, ref) => (
  <div
    ref={ref}
    role="alert"
    className={cn(alertVariants({ variant }), className)}
    {...props}
  />
));
Alert.displayName = 'Alert';

const AlertTitle = forwardRef<
  HTMLParagraphElement,
  HTMLAttributes<HTMLHeadingElement>
>(({ className, ...props }, ref) => (
  <h5
    ref={ref}
    className={cn('mb-1 font-medium leading-none tracking-tight', className)}
    {...props}
  />
));
AlertTitle.displayName = 'AlertTitle';

const AlertDescription = forwardRef<
  HTMLParagraphElement,
  HTMLAttributes<HTMLParagraphElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn('text-sm [&_p]:leading-relaxed', className)}
    {...props}
  />
));
AlertDescription.displayName = 'AlertDescription';

interface AlertWithIconProps extends HTMLAttributes<HTMLDivElement> {
  variant?: 'default' | 'destructive' | 'success' | 'warning';
  title?: string;
  description?: string;
  onClose?: () => void;
}

const AlertWithIcon = forwardRef<HTMLDivElement, AlertWithIconProps>(
  ({ className, variant = 'default', title, description, onClose, ...props }, ref) => {
    const icons = {
      default: Info,
      destructive: AlertCircle,
      success: CheckCircle,
      warning: AlertCircle,
    };

    const Icon = icons[variant];

    return (
      <Alert ref={ref} variant={variant} className={`flex items-start ${className}`} {...props}>
        <Icon className="h-4 w-4 mt-0.5 flex-shrink-0" />
        <div className="flex-1 ml-3">
          {title && <AlertTitle>{title}</AlertTitle>}
          {description && <AlertDescription>{description}</AlertDescription>}
        </div>
        {onClose && (
          <button
            onClick={onClose}
            className="ml-auto flex-shrink-0 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
          >
            <X className="h-4 w-4" />
            <span className="sr-only">Close</span>
          </button>
        )}
      </Alert>
    );
  }
);
AlertWithIcon.displayName = 'AlertWithIcon';

export { Alert, AlertTitle, AlertDescription, AlertWithIcon };
