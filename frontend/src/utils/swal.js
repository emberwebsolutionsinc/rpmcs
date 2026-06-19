import Swal from "sweetalert2";

export const confirmDelete = async (title = "Delete Record?") => {
    const result = await Swal.fire({
        title,
        text: "This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc2626",
        cancelButtonColor: "#64748b",
        confirmButtonText: "Delete",
    });

    return result.isConfirmed;
};