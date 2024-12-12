import React, { useState } from 'react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "../ui/dropdown-menu";
import { Button } from "../ui/button";
import {
    FileText,
    FileSpreadsheet,
    FileType,
    Download,
    Loader2
} from "lucide-react";

const ExportButton = ({
                          chartType,
                          startDate,
                          endDate,
                          className = ""
                      }) => {
    const [isExporting, setIsExporting] = useState(false);

    // Map format to file extension
    const getFileExtension = (format) => {
        const extensions = {
            'pdf': 'pdf',
            'excel': 'xlsx',
            'csv': 'csv'
        };
        return extensions[format] || format;
    };

    const handleExport = async (format) => {
        try {
            setIsExporting(true);
            const response = await fetch(`/api/export/revenue`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify({
                    format: format,
                    start_date: startDate,
                    end_date: endDate
                })
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Export failed');
            }

            // Handle file download
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            // Use the correct file extension
            a.download = `revenue_${startDate}_${endDate}.${getFileExtension(format)}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

        } catch (error) {
            console.error('Export error:', error);
        } finally {
            setIsExporting(false);
        }
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="outline"
                    className={className}
                    disabled={isExporting}
                >
                    {isExporting ? (
                        <Loader2 className="h-4 w-4 animate-spin mr-2" />
                    ) : (
                        <Download className="h-4 w-4 mr-2" />
                    )}
                    Export
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent>
                <DropdownMenuItem onClick={() => handleExport('pdf')}>
                    <FileText className="h-4 w-4 mr-2" />
                    <span>PDF</span>
                </DropdownMenuItem>
                <DropdownMenuItem onClick={() => handleExport('excel')}>
                    <FileSpreadsheet className="h-4 w-4 mr-2" />
                    <span>Excel</span>
                </DropdownMenuItem>
                <DropdownMenuItem onClick={() => handleExport('csv')}>
                    <FileType className="h-4 w-4 mr-2" />
                    <span>CSV</span>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
};

export default ExportButton;
