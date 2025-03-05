import React, {useEffect, useState} from 'react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "../ui/dialog";
import { Input } from "../ui/input";
import { Label } from "../ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "../ui/select";
import { Button } from "../ui/button";
import { Checkbox } from "../ui/checkbox";
import { useForm } from 'react-hook-form';

const NewUserModal = ({ open, onClose, onUserAdded }) => {
    const [isLoading, setIsLoading] = useState(false);
    const [roles, setRoles] = useState([
        {id: 1, name: 'Admin'},
        {id: 2, name: 'User'},
        // Fetch these from your API
    ]);

    const {
        register,
        handleSubmit,
        formState: {errors},
        reset
    } = useForm();

    const NewUserModal = ({open, onClose, onUserAdded}) => {
        const [isLoading, setIsLoading] = useState(false);
        const [roles, setRoles] = useState([]);
        const [error, setError] = useState(null);

        // Fetch roles when modal opens
        useEffect(() => {
            if (open) {
                fetchRoles();
            }
        }, [open]);

        const fetchRoles = async () => {
            try {
                const response = await fetch('/api/roles-list', {
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch roles');
                }

                const data = await response.json();
                setRoles(data);
            } catch (err) {
                console.error('Error fetching roles:', err);
                setError('Failed to load roles');
            }
        };

        const {
            register,
            handleSubmit,
            formState: {errors},
            reset,
            setError: setFormError
        } = useForm();

        const onSubmit = async (data) => {
            setIsLoading(true);
            setError(null);

            try {
                const response = await fetch('/api/users', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                    body: JSON.stringify({
                        ...data,
                        // Ensure DUI format is correct
                        dui: data.dui.replace(/[^0-9-]/g, ''),
                    })
                });

                if (response.ok) {
                    onUserAdded();
                    onClose();
                    reset();
                } else {
                    const errorData = await response.json();

                    // Handle validation errors from Laravel
                    if (response.status === 422) {
                        Object.keys(errorData.errors).forEach(key => {
                            setFormError(key, {
                                type: 'manual',
                                message: errorData.errors[key][0]
                            });
                        });
                    } else {
                        setError(errorData.message || 'Failed to create user');
                    }
                }
            } catch (err) {
                console.error('Error creating user:', err);
                setError('An unexpected error occurred');
            } finally {
                setIsLoading(false);
            }
        };

        // Display any API errors
        useEffect(() => {
            if (error) {
                // You might want to use a toast notification here
                console.error(error);
            }
        }, [error]);

        return (
            <Dialog open={open} onOpenChange={onClose}>
                <DialogContent className="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Nuevo Usuario</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
                        <div className="grid gap-4 py-4">
                            <div className="grid grid-cols-4 items-center gap-4">
                                <Label htmlFor="name" className="text-right">
                                    Nombre Completo
                                </Label>
                                <Input
                                    id="name"
                                    className="col-span-3"
                                    {...register("full_name", {required: true})}
                                />
                            </div>
                            <div className="grid grid-cols-4 items-center gap-4">
                                <Label htmlFor="dui" className="text-right">
                                    DUI
                                </Label>
                                <Input
                                    id="dui"
                                    className="col-span-3"
                                    {...register("dui", {
                                        required: true,
                                        pattern: /^[0-9]{8}-[0-9]$/
                                    })}
                                />
                            </div>
                            <div className="grid grid-cols-4 items-center gap-4">
                                <Label htmlFor="password" className="text-right">
                                    Contraseña
                                </Label>
                                <Input
                                    id="password"
                                    type="password"
                                    className="col-span-3"
                                    {...register("password", {required: true})}
                                />
                            </div>
                            <div className="grid grid-cols-4 items-center gap-4">
                                <Label htmlFor="role" className="text-right">
                                    Rol
                                </Label>
                                <Select
                                    onValueChange={(value) => register("role_id").onChange({target: {value}})}
                                    disabled={roles.length === 0}
                                >
                                    <SelectTrigger className="col-span-3">
                                        <SelectValue placeholder="Seleccionar rol"/>
                                    </SelectTrigger>
                                    <SelectContent>
                                        {roles.map((role) => (
                                            <SelectItem key={role.id} value={role.id.toString()}>
                                                {role.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="grid grid-cols-4 items-center gap-4">
                                <Label className="text-right">
                                    Estado
                                </Label>
                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_active"
                                        {...register("is_active")}
                                    />
                                    <label
                                        htmlFor="is_active"
                                        className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Usuario Activo
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div className="flex justify-end space-x-4">
                            <Button type="button" variant="outline" onClick={onClose}>
                                Cancelar
                            </Button>
                            <Button type="submit" disabled={isLoading}>
                                {isLoading ? "Guardando..." : "Guardar"}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        );
    };
}
export default NewUserModal;
