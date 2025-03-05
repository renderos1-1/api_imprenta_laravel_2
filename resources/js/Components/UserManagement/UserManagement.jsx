import React, { useState, useEffect } from 'react';
import { Button } from "../ui/button";
import { Input } from "../ui/input";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "../ui/table";
import { Search } from 'lucide-react';
import NewUserModal from './NewUserModal';
import NewRoleModal from './NewRoleModal';
import EditUserModal from './EditUserModal';
import DeleteUserDialog from './DeleteUserDialog';
import { Badge } from "../ui/badge";

const UserManagement = () => {
    const [users, setUsers] = useState([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [isNewUserModalOpen, setIsNewUserModalOpen] = useState(false);
    const [isNewRoleModalOpen, setIsNewRoleModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [selectedUser, setSelectedUser] = useState(null);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        fetchUsers();
    }, []);

    const fetchUsers = async () => {
        try {
            setIsLoading(true);
            const url = searchTerm
                ? `/api/users?search=${encodeURIComponent(searchTerm)}`
                : '/api/users';

            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch users');
            }

            const data = await response.json();
            setUsers(data.data); // Access the paginated data
            setIsLoading(false);
        } catch (error) {
            console.error('Error fetching users:', error);
            // You might want to show an error message to the user here
            setIsLoading(false);
        }
    };

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            fetchUsers();
        }, 300);

        return () => clearTimeout(timeoutId);
    }, [searchTerm]);

    // Initial fetch
    useEffect(() => {
        fetchUsers();
    }, []);

    const handleEdit = (user) => {
        setSelectedUser(user);
        setIsEditModalOpen(true);
    };

    const handleDelete = (user) => {
        setSelectedUser(user);
        setIsDeleteDialogOpen(true);
    };


    return (
        <div className="p-6 space-y-6">
            <div className="flex justify-between items-center">
                <h2 className="text-2xl font-bold">Administración de Usuarios</h2>
                <div className="space-x-4">
                    <Button variant="outline" onClick={() => setIsNewRoleModalOpen(true)}>
                        + Nuevo Rol
                    </Button>
                    <Button onClick={() => setIsNewUserModalOpen(true)}>
                        + Nuevo Usuario
                    </Button>
                </div>
            </div>

            <div className="relative">
                <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                <Input
                    placeholder="Buscar usuarios..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="pl-10"
                />
            </div>

            <div className="border rounded-lg">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nombre</TableHead>
                            <TableHead>DUI</TableHead>
                            <TableHead>Rol</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead className="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {isLoading ? (
                            <TableRow>
                                <TableCell colSpan={5} className="text-center">
                                    Loading users...
                                </TableCell>
                            </TableRow>
                        ) : users.length === 0 ? (
                            <TableRow>
                                <TableCell colSpan={5} className="text-center">
                                    No users found
                                </TableCell>
                            </TableRow>
                        ) : (
                            users.map((user) => (
                                <TableRow key={user.dui}>
                                    <TableCell className="font-medium">{user.full_name}</TableCell>
                                    <TableCell>{user.dui}</TableCell>
                                    <TableCell>{user.role?.name}</TableCell>
                                    <TableCell>
                                        <Badge
                                            variant={user.is_active ? "success" : "secondary"}
                                        >
                                            {user.is_active ? 'Activo' : 'Inactivo'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="text-right space-x-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => handleEdit(user)}
                                        >
                                            Editar
                                        </Button>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            onClick={() => handleDelete(user)}
                                        >
                                            Eliminar
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </div>

            <NewUserModal
                open={isNewUserModalOpen}
                onClose={() => setIsNewUserModalOpen(false)}
                onUserAdded={fetchUsers}
            />

            <NewRoleModal
                open={isNewRoleModalOpen}
                onClose={() => setIsNewRoleModalOpen(false)}
            />

            {selectedUser && (
                <>
                    <EditUserModal
                        open={isEditModalOpen}
                        onClose={() => {
                            setIsEditModalOpen(false);
                            setSelectedUser(null);
                        }}
                        user={selectedUser}
                        onUserUpdated={fetchUsers}
                    />

                    <DeleteUserDialog
                        open={isDeleteDialogOpen}
                        onClose={() => {
                            setIsDeleteDialogOpen(false);
                            setSelectedUser(null);
                        }}
                        user={selectedUser}
                        onUserDeleted={fetchUsers}
                    />
                </>
            )}
        </div>
    );
};

export default UserManagement;
