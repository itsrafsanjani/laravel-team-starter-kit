import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { router } from '@inertiajs/react';
import { AlertTriangle, Loader2 } from 'lucide-react';
import { useState } from 'react';

interface Plan {
  id: number;
  name: string;
  slug: string;
  description: string;
  type: 'free' | 'trial' | 'subscription' | 'lifetime';
  monthly_price: number | null | string;
  yearly_price: number | null | string;
  lifetime_price: number | null | string;
  trial_days: number;
  features: string[];
  permissions: Record<string, any>;
  is_active: boolean;
  is_popular: boolean;
  is_legacy: boolean;
  sort_order: number;
  created_at: string;
  subscriptions_count?: number;
  trials_count?: number;
}

interface DeletePlanModalProps {
  plan: Plan | null;
  isOpen: boolean;
  onClose: () => void;
}

export default function DeletePlanModal({
  plan,
  isOpen,
  onClose,
}: DeletePlanModalProps) {
  const [isDeleting, setIsDeleting] = useState(false);

  const handleDelete = async () => {
    if (!plan) return;

    setIsDeleting(true);

    try {
      await router.delete(`/admin/plans/${plan.id}`, {
        onSuccess: () => {
          onClose();
          setIsDeleting(false);
        },
        onError: () => {
          setIsDeleting(false);
        },
      });
    } catch (error) {
      setIsDeleting(false);
    }
  };

  const handleClose = () => {
    if (!isDeleting) {
      onClose();
    }
  };

  if (!plan) return null;

  return (
    <Dialog open={isOpen} onOpenChange={handleClose}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-destructive/10">
              <AlertTriangle className="h-5 w-5 text-destructive" />
            </div>
            <div>
              <DialogTitle>Delete Plan</DialogTitle>
              <DialogDescription>
                This action cannot be undone. This will permanently delete the
                plan and remove all associated data.
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <div className="py-4">
          <div className="rounded-lg border border-destructive/20 bg-destructive/5 p-4">
            <div className="font-medium text-destructive">
              Plan: {plan.name}
            </div>
            <div className="mt-1 text-sm text-muted-foreground">
              Type: {plan.type.charAt(0).toUpperCase() + plan.type.slice(1)}
              {plan.is_legacy && ' (Legacy)'}
            </div>
            {plan.subscriptions_count && plan.subscriptions_count > 0 && (
              <div className="mt-1 text-sm text-muted-foreground">
                ⚠️ This plan has {plan.subscriptions_count} active
                subscription(s)
              </div>
            )}
            {plan.trials_count && plan.trials_count > 0 && (
              <div className="mt-1 text-sm text-muted-foreground">
                ⚠️ This plan has {plan.trials_count} active trial(s)
              </div>
            )}
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" onClick={handleClose} disabled={isDeleting}>
            Cancel
          </Button>
          <Button
            variant="destructive"
            onClick={handleDelete}
            disabled={isDeleting}
          >
            {isDeleting ? (
              <>
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                Deleting...
              </>
            ) : (
              'Delete Plan'
            )}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
