import React, { useState, useEffect } from 'react';
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

const EditUserModal = ({ open, onClose, user, onUserUpdated }) => {
    const [isLoading, setIsLoading] = useState(false);
    const [roles, setRoles] = useState([]);
    const [error, setError] = useState(null);

    const {
        register,
        handleSubmit,
        formState: { errors },
        reset,
        setValue,
        setError: setFormError
    } = useForm();

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

    // Set initial form values when user data is available
    useEffect(() => {
        if (user) {
            reset({
                full_name: user.full_name,
                dui: user.dui,
                role_id: user.role_id?.toString(),
                is_active: user.is_active
            });
        }
    }, [user, reset]);

    const onSubmit = async (data) => {
        setIsLoading(true);
        setError(null);

        try {
            const response = await fetch(`/api/users/${user.dui}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify({
                    ...data,
                    // Only include password if it was changed
                    password: data.password || undefined
                })
            });

            if (response.ok) {
                onUserUpdated();
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
                } else if (response.status === 403) {
                    setError('No tiene permisos para editar usuarios');
                } else {
                    setError(errorData.message || 'Error al actualizar el usuario');
                }
            }
        } catch (err) {
            console.error('Error:', err);
            setError('Ocurrió un error inesperado');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Editar Usuario</DialogTitle>
                </DialogHeader>
                {error && (
                    <div className="text-red-600 text-sm p-2 bg-red-50 rounded-md">
                        {error}
                    </div>
                )}
                <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="name" className="text-right">
                                Nombre Completo
                            </Label>
                            <div className="col-span-3">
                                <Input
                                    id="name"
                                    className={`${errors.full_name ? 'border-red-500' : ''}`}
                                    {...register("full_name", {
                                        required: "El nombre completo es requerido"
                                    })}
                                />
                                {errors.full_name && (
                                    <span className="text-sm text-red-500">
                                        {errors.full_name.message}
                                    </span>
                                )}
                            </div>
                        </div>

                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="dui" className="text-right">
                                DUI
                            </Label>
                            <div className="col-span-3">
                                <Input
                                    id="dui"
                                    disabled
                                    className="bg-gray-100"
                                    {...register("dui")}
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="password" className="text-right">
                                Contraseña
                            </Label>
                            <div className="col-span-3">
                                <Input
                                    id="password"
                                    type="password"
                                    placeholder="Dejar en blanco para mantener la contraseña actual"
                                    className={`${errors.password ? 'border-red-500' : ''}`}
                                    {...register("password", {
                                        minLength: {
                                            value: 8,
                                            message: "La contraseña debe tener al menos 8 caracteres"
                                        }
                                    })}
                                />
                                {errors.password && (
                                    <span className="text-sm text-red-500">
                                        {errors.password.message}
                                    </span>
                                )}
                            </div>
                        </div>

                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="role" className="text-right">
                                Rol
                            </Label>
                            <div className="col-span-3">
                                <Select
                                    onValueChange={(value) => setValue("role_id", value)}
                                    defaultValue={user?.role_id?.toString()}
                                    disabled={roles.length === 0}
                                >
                                    <SelectTrigger className={`${errors.role_id ? 'border-red-500' : ''}`}>
                                        <SelectValue placeholder="Seleccionar rol" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {roles.map((role) => (
                                            <SelectItem key={role.id} value={role.id.toString()}>
                                                {role.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.role_id && (
                                    <span className="text-sm text-red-500">
                                        {errors.role_id.message}
                                    </span>
                                )}
                            </div>
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
                                    className="text-sm font-medium leading-none"
                                >
                                    Usuario Activo
                                </label>
                            </div>
                        </div>
                    </div>

                    <div className="flex justify-end space-x-4">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                            disabled={isLoading}
                        >
                            Cancelar
                        </Button>
                        <Button
                            type="submit"
                            disabled={isLoading || roles.length === 0}
                        >
                            {isLoading ? (
                                <span className="flex items-center gap-2">
                                    <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                    </svg>
                                    Guardando...
                                </span>
                            ) : (
                                "Guardar"
                            )}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
};

export default EditUserModal;
