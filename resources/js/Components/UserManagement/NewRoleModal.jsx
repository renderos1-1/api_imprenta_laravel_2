import React, { useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "../ui/dialog";
import { Input } from "../ui/input";
import { Label } from "../ui/label";
import { Button } from "../ui/button";
import { Checkbox } from "../ui/checkbox";
import { Textarea } from "../ui/textarea";
import { ScrollArea } from "../ui/scroll-area";
import { useForm } from 'react-hook-form';

const NewRoleModal = ({ open, onClose }) => {
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState(null);
    const [permissions, setPermissions] = useState([]);

    // Fetch permissions when modal opens
    useEffect(() => {
        if (open) {
            fetchPermissions();
        }
    }, [open]);

    const fetchPermissions = async () => {
        try {
            const response = await fetch('/api/permissions-list', {
                headers: {
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch permissions');
            }

            const data = await response.json();
            setPermissions(data);
        } catch (err) {
            console.error('Error fetching permissions:', err);
            setError('Failed to load permissions');
        }
    };

    const {
        register,
        handleSubmit,
        formState: { errors },
        reset,
        setValue,
        watch,
        setError: setFormError
    } = useForm({
        defaultValues: {
            name: '',
            description: '',
            permissions: []
        }
    });

    const onSubmit = async (data) => {
        setIsLoading(true);
        setError(null);

        try {
            const response = await fetch('/api/roles', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify({
                    name: data.name,
                    description: data.description,
                    permissions: data.permissions
                })
            });

            if (response.ok) {
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
                    setError(errorData.message || 'Failed to create role');
                }
            }
        } catch (err) {
            console.error('Error creating role:', err);
            setError('An unexpected error occurred');
        } finally {
            setIsLoading(false);
        }
    };

    const handleCheckboxChange = (permissionId) => {
        const currentPermissions = watch('permissions') || [];
        const updatedPermissions = currentPermissions.includes(permissionId)
            ? currentPermissions.filter(id => id !== permissionId)
            : [...currentPermissions, permissionId];

        setValue('permissions', updatedPermissions);
    };

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>Nuevo Rol</DialogTitle>
                </DialogHeader>
                <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="name" className="text-right">
                                Nombre del Rol
                            </Label>
                            <div className="col-span-3">
                                <Input
                                    id="name"
                                    className={`${errors.name ? 'border-red-500' : ''}`}
                                    {...register("name", {
                                        required: "El nombre del rol es requerido"
                                    })}
                                />
                                {errors.name && (
                                    <span className="text-sm text-red-500">
                                        {errors.name.message}
                                    </span>
                                )}
                            </div>
                        </div>

                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="description" className="text-right">
                                Descripción
                            </Label>
                            <div className="col-span-3">
                                <Textarea
                                    id="description"
                                    className={`${errors.description ? 'border-red-500' : ''}`}
                                    {...register("description")}
                                />
                                {errors.description && (
                                    <span className="text-sm text-red-500">
                                        {errors.description.message}
                                    </span>
                                )}
                            </div>
                        </div>

                        <div className="grid grid-cols-4 items-start gap-4">
                            <Label className="text-right pt-2">
                                Permisos de Acceso
                            </Label>
                            <div className="col-span-3">
                                {error && (
                                    <div className="text-red-600 text-sm mb-2">
                                        {error}
                                    </div>
                                )}
                                <ScrollArea className="h-72 border rounded-md p-4">
                                    {permissions.length === 0 ? (
                                        <div className="text-center text-gray-500">
                                            Cargando permisos...
                                        </div>
                                    ) : (
                                        <div className="space-y-4">
                                            {permissions.map((permission) => (
                                                <div key={permission.id} className="flex items-start space-x-3">
                                                    <Checkbox
                                                        id={permission.id}
                                                        onCheckedChange={() => handleCheckboxChange(permission.id)}
                                                    />
                                                    <div>
                                                        <label
                                                            htmlFor={permission.id}
                                                            className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                                        >
                                                            {permission.name}
                                                        </label>
                                                        <p className="text-sm text-gray-500">
                                                            {permission.description}
                                                        </p>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </ScrollArea>
                                {errors.permissions && (
                                    <span className="text-sm text-red-500">
                                        Debe seleccionar al menos un permiso
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                    <div className="flex justify-end space-x-4">
                        <Button type="button" variant="outline" onClick={onClose}>
                            Cancelar
                        </Button>
                        <Button type="submit" disabled={isLoading || permissions.length === 0}>
                            {isLoading ? "Guardando..." : "Guardar"}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
};

export default NewRoleModal;
