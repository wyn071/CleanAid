import * as XLSX from 'xlsx';

export interface BeneficiaryRecord {
  id: string;
  name: string;
  address: string;
  contact: string;
  program: string;
  amount: number;
  date: string;
  status: string;
  [key: string]: any; // For additional fields
}

export interface DataIssue {
  type: 'duplicate' | 'missing' | 'mismatch' | 'invalid';
  field: string;
  value: any;
  severity: 'high' | 'medium' | 'low';
  description: string;
  recordId: string;
}

export interface ProcessingResult {
  records: BeneficiaryRecord[];
  issues: DataIssue[];
  statistics: {
    totalRecords: number;
    duplicatesFound: number;
    missingData: number;
    mismatches: number;
    invalidEntries: number;
  };
}

class DataProcessingService {
  // Phase 1: Initial Scan
  async scanDataset(file: File): Promise<ProcessingResult> {
    const records = await this.parseFile(file);
    const issues: DataIssue[] = [];
    
    // Check for duplicates
    const duplicates = this.findDuplicates(records);
    issues.push(...duplicates);
    
    // Check for missing data
    const missingData = this.findMissingData(records);
    issues.push(...missingData);
    
    // Check for mismatches
    const mismatches = this.findMismatches(records);
    issues.push(...mismatches);
    
    return {
      records,
      issues,
      statistics: {
        totalRecords: records.length,
        duplicatesFound: duplicates.length,
        missingData: missingData.length,
        mismatches: mismatches.length,
        invalidEntries: issues.length
      }
    };
  }

  // Phase 2: AI-based Categorization
  async categorizeIssues(issues: DataIssue[]): Promise<DataIssue[]> {
    return issues.map(issue => {
      // Apply AI-based categorization logic
      const severity = this.determineSeverity(issue);
      return {
        ...issue,
        severity,
        description: this.generateDescription(issue)
      };
    });
  }

  // Phase 3: Review Preparation
  async prepareForReview(records: BeneficiaryRecord[], issues: DataIssue[]): Promise<{
    flaggedRecords: BeneficiaryRecord[];
    suggestedActions: Map<string, string>;
  }> {
    const flaggedRecords = records.filter(record => 
      issues.some(issue => issue.recordId === record.id)
    );

    const suggestedActions = new Map<string, string>();
    issues.forEach(issue => {
      const action = this.suggestAction(issue);
      suggestedActions.set(issue.recordId, action);
    });

    return {
      flaggedRecords,
      suggestedActions
    };
  }

  // Helper Methods
  private async parseFile(file: File): Promise<BeneficiaryRecord[]> {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = (e) => {
        try {
          const data = e.target?.result;
          const workbook = XLSX.read(data, { type: 'binary' });
          const sheetName = workbook.SheetNames[0];
          const worksheet = workbook.Sheets[sheetName];
          const rawRecords = XLSX.utils.sheet_to_json(worksheet);
          
          // Transform raw records into properly structured BeneficiaryRecord objects
          const records = rawRecords.map((record: any, index: number) => ({
            id: record.id || `REC-${index + 1}`, // Ensure each record has an ID
            name: record.name || '',
            address: record.address || '',
            contact: record.contact || '',
            program: record.program || '',
            amount: typeof record.amount === 'number' ? record.amount : 0,
            date: record.date || new Date().toISOString().split('T')[0],
            status: record.status || 'active',
            ...record // Include any additional fields
          }));

          resolve(records as BeneficiaryRecord[]);
        } catch (error) {
          reject(error);
        }
      };
      reader.onerror = reject;
      reader.readAsBinaryString(file);
    });
  }

  private findDuplicates(records: BeneficiaryRecord[]): DataIssue[] {
    const issues: DataIssue[] = [];
    const seen = new Map<string, number>();

    records.forEach((record, index) => {
      const key = `${record.name}-${record.address}`;
      if (seen.has(key)) {
        issues.push({
          type: 'duplicate',
          field: 'name,address',
          value: key,
          severity: 'high',
          description: `Duplicate entry found with record at index ${seen.get(key)}`,
          recordId: record.id
        });
      } else {
        seen.set(key, index);
      }
    });

    return issues;
  }

  private findMissingData(records: BeneficiaryRecord[]): DataIssue[] {
    const issues: DataIssue[] = [];
    const requiredFields = ['name', 'address', 'contact', 'program', 'amount'];

    records.forEach(record => {
      requiredFields.forEach(field => {
        if (!record[field]) {
          issues.push({
            type: 'missing',
            field,
            value: null,
            severity: 'high',
            description: `Missing required field: ${field}`,
            recordId: record.id
          });
        }
      });
    });

    return issues;
  }

  private findMismatches(records: BeneficiaryRecord[]): DataIssue[] {
    const issues: DataIssue[] = [];

    records.forEach(record => {
      // Check amount format
      if (record.amount && isNaN(Number(record.amount))) {
        issues.push({
          type: 'mismatch',
          field: 'amount',
          value: record.amount,
          severity: 'medium',
          description: 'Invalid amount format',
          recordId: record.id
        });
      }

      // Check date format
      if (record.date && !this.isValidDate(record.date)) {
        issues.push({
          type: 'mismatch',
          field: 'date',
          value: record.date,
          severity: 'medium',
          description: 'Invalid date format',
          recordId: record.id
        });
      }
    });

    return issues;
  }

  private determineSeverity(issue: DataIssue): 'high' | 'medium' | 'low' {
    switch (issue.type) {
      case 'duplicate':
        return 'high';
      case 'missing':
        return issue.field === 'amount' ? 'high' : 'medium';
      case 'mismatch':
        return 'medium';
      default:
        return 'low';
    }
  }

  private generateDescription(issue: DataIssue): string {
    switch (issue.type) {
      case 'duplicate':
        return `Duplicate entry detected for ${issue.field}`;
      case 'missing':
        return `Missing required field: ${issue.field}`;
      case 'mismatch':
        return `Invalid format for ${issue.field}`;
      default:
        return `Issue detected in ${issue.field}`;
    }
  }

  private suggestAction(issue: DataIssue): string {
    switch (issue.type) {
      case 'duplicate':
        return 'Review and merge if same beneficiary';
      case 'missing':
        return 'Fill in missing information';
      case 'mismatch':
        return 'Correct format';
      default:
        return 'Review and update';
    }
  }

  private isValidDate(dateString: string): boolean {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date.getTime());
  }
}

export default new DataProcessingService(); 