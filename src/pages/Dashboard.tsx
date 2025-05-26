import React, { useState } from 'react';
import { Card, Row, Col, Button, ProgressBar, Badge, Alert } from 'react-bootstrap';
import { Link } from 'react-router-dom';
import { Line } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
);

interface Activity {
  type: 'upload' | 'review' | 'export';
  description: string;
  timestamp: string;
  status?: 'success' | 'warning' | 'error';
}

interface DatasetStats {
  totalDatasets: number;
  duplicatesDetected: number;
  flaggedRecords: number;
  cleanedRecords: number;
  processingQueue: number;
  lastUpdated: string;
}

const Dashboard: React.FC = () => {
  const [stats, setStats] = useState<DatasetStats>({
    totalDatasets: 24,
    duplicatesDetected: 156,
    flaggedRecords: 42,
    cleanedRecords: 1234,
    processingQueue: 3,
    lastUpdated: new Date().toLocaleString()
  });

  const [recentActivity, setRecentActivity] = useState<Activity[]>([
    {
      type: 'upload',
      description: 'Uploaded Ayuda beneficiary list for SAP',
      timestamp: '2 hours ago',
      status: 'success'
    },
    {
      type: 'review',
      description: 'Reviewed and resolved 15 flagged records',
      timestamp: '3 hours ago',
      status: 'warning'
    },
    {
      type: 'export',
      description: 'Exported cleaned data for May 2025 batch',
      timestamp: '5 hours ago',
      status: 'success'
    }
  ]);

  const [timeFrame, setTimeFrame] = useState<'week' | 'month' | 'year'>('week');

  const chartData = {
    labels: timeFrame === 'week' ? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] :
            timeFrame === 'month' ? ['Week 1', 'Week 2', 'Week 3', 'Week 4'] :
            ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [
      {
        label: 'Processed Records',
        data: timeFrame === 'week' ? [90, 140, 130, 160, 180, 220, 314] :
              timeFrame === 'month' ? [320, 550, 670, 740] :
              [1200, 1340, 1420, 1780, 2000, 2340],
        borderColor: 'rgb(220, 53, 69)',
        backgroundColor: 'rgba(220, 53, 69, 0.5)',
        tension: 0.4
      }
    ]
  };

  const chartOptions = {
    responsive: true,
    plugins: {
      legend: {
        position: 'top' as const
      },
      title: {
        display: true,
        text: `Data Processed (${timeFrame.charAt(0).toUpperCase() + timeFrame.slice(1)})`
      }
    },
    scales: {
      y: {
        beginAtZero: true
      }
    }
  };

  const handleRefresh = () => {
    setStats(prev => ({
      ...prev,
      lastUpdated: new Date().toLocaleString()
    }));

    // Optional: Add mock activity
    setRecentActivity(prev => [
      {
        type: 'upload',
        description: 'Refreshed data for regional barangay list',
        timestamp: 'Just now',
        status: 'success'
      },
      ...prev
    ]);
  };

  return (
    <div className="container-fluid">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 className="mb-1">Welcome to CleanAid</h2>
          <p className="text-muted mb-0">Your data cleaning and management dashboard</p>
        </div>
        <div className="text-end">
          <small className="text-muted">Last updated: {stats.lastUpdated}</small>
          <Button variant="outline-danger" size="sm" className="ms-2" onClick={handleRefresh}>
            <i className="bi bi-arrow-clockwise me-1"></i>
            Refresh
          </Button>
        </div>
      </div>

      {stats.processingQueue > 0 && (
        <Alert variant="warning" className="mb-4">
          <i className="bi bi-hourglass-split me-2"></i>
          {stats.processingQueue} datasets are currently being processed
        </Alert>
      )}

      <Row className="g-4 mb-4">
        {[
          { title: 'Total Datasets', value: stats.totalDatasets, variant: 'primary', icon: 'bi-database', progress: 75 },
          { title: 'Duplicates Detected', value: stats.duplicatesDetected, variant: 'warning', icon: 'bi-exclamation-triangle', progress: 60 },
          { title: 'Flagged Records', value: stats.flaggedRecords, variant: 'danger', icon: 'bi-flag', progress: 30 },
          { title: 'Cleaned Records', value: stats.cleanedRecords, variant: 'success', icon: 'bi-check-circle', progress: 90 }
        ].map((card, i) => (
          <Col md={3} key={i}>
            <Card className="border-0 shadow-sm h-100">
              <Card.Body>
                <div className="d-flex align-items-center mb-3">
                  <div className={`bg-${card.variant} bg-opacity-10 p-3 rounded me-3`}>
                    <i className={`bi ${card.icon} text-${card.variant} fs-4`}></i>
                  </div>
                  <div>
                    <h6 className="mb-0">{card.title}</h6>
                    <h3 className="mb-0">{card.value}</h3>
                    <small className="text-muted">Auto-updated</small>
                  </div>
                </div>
                <ProgressBar now={card.progress} variant={card.variant} className="mt-2" />
              </Card.Body>
            </Card>
          </Col>
        ))}
      </Row>

      <Row className="g-4 mb-4">
        <Col md={8}>
          <Card className="border-0 shadow-sm h-100">
            <Card.Body>
              <div className="d-flex justify-content-between align-items-center mb-4">
                <h5 className="mb-0">Processing Trends</h5>
                <div className="btn-group">
                  {['week', 'month', 'year'].map(period => (
                    <Button
                      key={period}
                      variant={timeFrame === period ? 'danger' : 'outline-danger'}
                      size="sm"
                      onClick={() => setTimeFrame(period as typeof timeFrame)}
                    >
                      {period.charAt(0).toUpperCase() + period.slice(1)}
                    </Button>
                  ))}
                </div>
              </div>
              <div style={{ height: '300px' }}>
                <Line options={chartOptions} data={chartData} />
              </div>
            </Card.Body>
          </Card>
        </Col>
        <Col md={4}>
          <Card className="border-0 shadow-sm h-100">
            <Card.Body>
              <h5 className="mb-4">Quick Actions</h5>
              <div className="d-grid gap-2">
                <Link to="/upload" className="btn btn-danger btn-lg">
                  <i className="bi bi-cloud-upload me-2"></i>
                  Upload New Dataset
                </Link>
                <Link to="/review" className="btn btn-outline-danger btn-lg">
                  <i className="bi bi-flag me-2"></i>
                  Review Flagged Data
                  {stats.flaggedRecords > 0 && <Badge bg="danger" className="ms-2">{stats.flaggedRecords}</Badge>}
                </Link>
                <Link to="/export" className="btn btn-outline-danger btn-lg">
                  <i className="bi bi-download me-2"></i>
                  Export Data
                </Link>
              </div>
            </Card.Body>
          </Card>
        </Col>
      </Row>

      <Row className="g-4">
        <Col md={12}>
          <Card className="border-0 shadow-sm">
            <Card.Body>
              <div className="d-flex justify-content-between align-items-center mb-4">
                <h5 className="mb-0">Recent Activity</h5>
                <Button variant="outline-danger" size="sm">View All Activity</Button>
              </div>
              {recentActivity.length === 0 ? (
                <p className="text-muted">No recent activity.</p>
              ) : (
                <div className="activity-feed">
                  {recentActivity.map((activity, index) => (
                    <div key={index} className="activity-item d-flex align-items-start mb-3">
                      <div className={`activity-icon me-3 p-2 rounded ${
                        activity.type === 'upload' ? 'bg-primary bg-opacity-10 text-primary' :
                        activity.type === 'review' ? 'bg-warning bg-opacity-10 text-warning' :
                        'bg-success bg-opacity-10 text-success'
                      }`}>
                        <i className={`bi ${
                          activity.type === 'upload' ? 'bi-cloud-upload' :
                          activity.type === 'review' ? 'bi-flag' :
                          'bi-download'
                        }`}></i>
                      </div>
                      <div className="flex-grow-1">
                        <div className="d-flex justify-content-between align-items-start">
                          <p className="mb-1">{activity.description}</p>
                          <Badge bg={
                            activity.status === 'success' ? 'success' :
                            activity.status === 'warning' ? 'warning' :
                            'danger'
                          } className="ms-2">
                            {activity.status}
                          </Badge>
                        </div>
                        <small className="text-muted">{activity.timestamp}</small>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </div>
  );
};

export default Dashboard;
