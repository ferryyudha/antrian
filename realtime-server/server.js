const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');
const axios = require('axios');

const app = express();
app.use(cors());
app.use(express.json());

const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

const LARAVEL_API_URL = 'http://localhost:8000/api';

io.on('connection', (socket) => {
    console.log('A user connected:', socket.id);

    // Clients will join a specific location room
    socket.on('join_location', (locationId) => {
        const room = `queue_update_${locationId}`;
        socket.join(room);
        console.log(`Socket ${socket.id} joined room ${room}`);
    });

    socket.on('disconnect', () => {
        console.log('User disconnected:', socket.id);
    });
});

// Endpoint to be called by Laravel to trigger a broadcast
app.post('/broadcast', async (req, res) => {
    const { location_id } = req.body;

    if (!location_id) {
        return res.status(400).json({ error: 'location_id is required' });
    }

    try {
        // Fetch fresh status from Laravel API
        const response = await axios.get(`${LARAVEL_API_URL}/queue-status/${location_id}`);
        
        if (response.data && response.data.status === 'success') {
            const dataToBroadcast = response.data.data;
            const room = `queue_update_${location_id}`;
            
            // Broadcast to all clients in the room
            io.to(room).emit('queue_updated', dataToBroadcast);
            console.log(`Broadcasted to ${room}:`, dataToBroadcast);
            
            return res.json({ status: 'success', message: 'Broadcast successful' });
        }
        
        return res.status(500).json({ error: 'Invalid response from Laravel API' });
    } catch (error) {
        console.error('Error fetching data from Laravel:', error.message);
        return res.status(500).json({ error: 'Failed to broadcast' });
    }
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`Realtime Server running on port ${PORT}`);
});
