require("dotenv").config({ path: "C:/xampp/htdocs/clipora/.env" });
const { Pool } = require("pg");
const pool = new Pool({
    connectionString: process.env.DATABASE_URL,
    ssl: { rejectUnauthorized: false }
});

(async () => {
    try {
        const r = await pool.query(`
            SELECT id, name, created_at 
            FROM autoclip_projects 
            WHERE user_id = 1 
            ORDER BY created_at DESC LIMIT 5
        `);
        
        console.log("=".repeat(60));
        console.log("PROJECTS di DB:");
        console.log("=".repeat(60));
        r.rows.forEach(row => {
            console.log(`  ID: ${row.id}`);
            console.log(`  Length: ${row.id.length}`);
            console.log(`  Name: ${row.name}`);
            console.log(`  Created: ${row.created_at}`);
            console.log("");
        });
    } catch (e) {
        console.error("ERROR:", e.message);
    }
    process.exit(0);
})();
