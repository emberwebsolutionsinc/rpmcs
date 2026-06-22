import Swal from "sweetalert2";

export const confirmDelete = async (message = "Are you sure?") => {
    const result = await Swal.fire({
        title: "Are you sure?",
        text: message,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, proceed",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#dc2626",
        cancelButtonColor: "#64748b",
        customClass: {
            popup: "rpmcs-swal-popup",
            container: "rpmcs-swal-container",
        },
    });

    return result.isConfirmed;
};