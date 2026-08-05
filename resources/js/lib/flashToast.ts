import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

type FlashProps = {
    success?: string | null;
    error?: string | null;
};

export function initializeFlashToast(): void {
    router.on('success', (event) => {
        const flash = event.detail.page.props.flash as FlashProps | undefined;

        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    });
}
